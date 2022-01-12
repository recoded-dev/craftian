<?php

namespace Recoded\Craftian\Repositories\Software;

use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use Recoded\Craftian\Configuration\ChecksumType;
use Recoded\Craftian\Configuration\ConfigurationType;
use Recoded\Craftian\Http\Client;
use Recoded\Craftian\Repositories\SoftwareRepository;

abstract class PaperMcRepository extends SoftwareRepository
{
    /**
     * @var array<array<string, mixed>>
     */
    protected array $configurations;

    /**
     * @return array<array<string, mixed>>
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     */
    protected function fetchConfigurations(): array
    {
        if (isset($this->configurations)) {
            return $this->configurations;
        }

        $client = new Client();

        $versions = $client->json('get', $this->url())->versions;

        $versions = array_map(fn (string $version) => [
            'name' => static::getName(),
            'type' => ConfigurationType::Software->value,
            'version' => $version,
        ], $versions);

        $requests = function () use ($versions) {
            foreach ($versions as ['version' => $version]) {
                yield new Request('get', $this->url("versions/{$version}"));
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
                yield new Request('get', $this->url(
                    "versions/{$version['version']}/builds/{$version['build']}",
                ));
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
                $versions[$index]['distribution']['url'] = $this->url(
                    "versions/{$version['version']}/builds/{$version['build']}/downloads/{$download->name}",
                );

                unset($versions[$index]['build']);
            },
            'rejected' => function ($reason, $index) use (&$versions) {
                unset($versions[$index]);
            },
        ]);

        $promise = $pool->promise();
        $promise->wait();

        return $this->configurations = $versions;
    }

    public function get(string $name): array
    {
        if ($name !== static::getName()) {
            return [];
        }

        return array_map(
            fn (array $config) => ConfigurationType::Software->initialize($config),
            $this->fetchConfigurations(),
        );
    }

    protected function url(string $append = ''): string
    {
        return sprintf('https://papermc.io/api/v2/projects/%s/%s', $this->project(), $append);
    }

    abstract protected function project(): string;
}
