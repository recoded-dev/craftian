<?php

namespace Recoded\Craftian\Repositories;

use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use Recoded\Craftian\Configuration\ChecksumType;
use Recoded\Craftian\Configuration\ConfigurationType;
use Recoded\Craftian\Http\Client;

class PaperRepository extends SoftwareRepository
{
    protected static array $configurations;

    protected static function fetchConfigurations(): array
    {
        if (isset(static::$configurations)) {
            return static::$configurations;
        }

        $client = new Client();

        $versions = $client->json('get', 'https://papermc.io/api/v2/projects/paper')->versions;

        $versions = array_map(fn (string $version) => [
            'name' => static::getName(),
            'type' => ConfigurationType::Software->value,
            'version' => $version,
        ], $versions);

        $requests = function () use ($versions) {
            foreach ($versions as ['version' => $version]) {
                yield new Request('get', "https://papermc.io/api/v2/projects/paper/versions/{$version}");
            }
        };

        $pool = new Pool($client, $requests(), [
            'concurrency' => 10,
            'fulfilled' => function (ResponseInterface $response, int $index) use (&$versions) {
                $versions[$index]['build'] = max(json_decode(
                    $response->getBody(),
                    flags: JSON_THROW_ON_ERROR,
                )->builds);
            },
            'rejected' => function ($reason, $index) use (&$versions) {
                unset($versions[$index]);
            },
        ]);

        $promise = $pool->promise();
        $promise->wait();

        $requests = function () use ($versions) {
            foreach ($versions as $version) {
                yield new Request('get', "https://papermc.io/api/v2/projects/paper/versions/{$version['version']}/builds/{$version['build']}");
            }
        };

        $pool = new Pool($client, $requests(), [
            'concurrency' => 10,
            'fulfilled' => function (ResponseInterface $response, int $index) use (&$versions) {
                $download = json_decode(
                    $response->getBody(),
                    flags: JSON_THROW_ON_ERROR,
                )->downloads->application;

                $version = $versions[$index];

                $versions[$index]['distribution']['checksum-type'] = ChecksumType::Sha256->value;
                $versions[$index]['distribution']['checksum'] = $download->sha256;
                $versions[$index]['distribution']['url'] = "https://papermc.io/api/v2/projects/paper/versions/{$version['version']}/builds/{$version['build']}/downloads/{$download->name}";

                unset($versions[$index]['build']);
            },
            'rejected' => function ($reason, $index) use (&$versions) {
                unset($versions[$index]);
            },
        ]);

        $promise = $pool->promise();
        $promise->wait();

        return static::$configurations = $versions;
    }

    public function get(string $name): array
    {
        if ($name !== static::getName()) {
            return [];
        }

        return array_map(
            fn (array $config) => ConfigurationType::Software->initialize($config),
            static::fetchConfigurations(),
        );
    }

    public static function getName(): string
    {
        return 'papermc/paper';
    }
}
