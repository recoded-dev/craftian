<?php

namespace Recoded\Craftian\Repositories\Software;

use Recoded\Craftian\Configuration\ChecksumType;
use Recoded\Craftian\Configuration\ConfigurationType;
use Recoded\Craftian\Http\Client;
use Recoded\Craftian\Repositories\SoftwareRepository;

abstract class ServerJarsRepository extends SoftwareRepository
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

        $versions = $client->json('get', $this->url())->response;

        $versions = array_map(fn (object $version) => [
            'distribution' => [
                'checksum' => $version->md5,
                'checksum-type' => ChecksumType::Md5->value,
                'url' => sprintf('https://serverjars.com/api/fetchJar/%s/%s', $this->type(), $version->version),
            ],
            'name' => static::getName(),
            'type' => ConfigurationType::Software->value,
            'version' => $version->version,
        ], $versions);

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

    protected function url(): string
    {
        return sprintf('https://serverjars.com/api/fetchAll/%s/1000', $this->type());
    }

    abstract protected function type(): string;
}
