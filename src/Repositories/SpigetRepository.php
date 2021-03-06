<?php

namespace Recoded\Craftian\Repositories;

use Recoded\Craftian\Configuration\BlueprintType;
use Recoded\Craftian\Configuration\ChecksumType;
use Recoded\Craftian\Contracts\Repository;
use Recoded\Craftian\Http\Client;

class SpigetRepository implements Repository
{
    protected const PAGE_SIZE = 500;

    /**
     * @var array<int>
     */
    protected static array $ids;

    /**
     * @inheritDoc
     */
    public function autocomplete(string $query): array
    {
        if (!str_starts_with($query, 'spiget/')) {
            return [];
        }

        return array_filter(
            $this->provides()[BlueprintType::Plugin->value],
            fn (string $name) => str_starts_with($name, $query),
        );
    }

    /**
     * @return array<int>
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     */
    protected function fetchIds(): array
    {
        if (isset(static::$ids)) {
            return static::$ids;
        }

        static::$ids = [];
        $page = 0;

        $client = new Client();

        do {
            /** @var array<object> $data */
            $data = $client->json('get', sprintf(
                'https://api.spiget.org/v2/resources?size=%d&fields=id&page=%d',
                static::PAGE_SIZE,
                $page++,
            ));

            static::$ids = array_merge(static::$ids, array_filter(array_map(function (object $resource) {
                return isset($resource->id) ? $resource->id : null; // phpstan doesn't understand ?? coalesce
            }, $data)));

            $lastResultCount = count($data);
        } while ($lastResultCount === static::PAGE_SIZE);

        return static::$ids;
    }

    /**
     * @inheritDoc
     */
    public function get(string $name): array
    {
        if (!preg_match('/^spiget\/(\d+)$/i', $name, $matches)) {
            return [];
        }

        $id = (int) $matches[1];

        $client = new Client();
        $page = 0;

        $versions = [];

        do {
            /** @var array<object> $data */
            $data = $client->json('get', sprintf(
                'https://api.spiget.org/v2/resources/%d/versions?size=%d&page=%d',
                $id,
                static::PAGE_SIZE,
                $page++,
            ));

            $versions = array_merge($versions, $data);
            $lastResultCount = count($data);
        } while ($lastResultCount === static::PAGE_SIZE);

        return array_map([BlueprintType::Plugin, 'initialize'], array_filter(
            array_map(function (object $version) use ($id) {
                if (!isset($version->id, $version->name)) {
                    return null;
                }

                return [
                    'distribution' => [
                        'url' => sprintf(
                            'https://api.spiget.org/v2/resources/%d/versions/%d/download',
                            $id,
                            $version->id,
                        ),
                        'checksum-type' => ChecksumType::None->value,
                    ],
                    'name' => 'spiget/' . $id,
                    'type' => BlueprintType::Plugin->value,
                    'version' => $version->name,
                ];
            }, $versions),
        ));
    }

    /**
     * @inheritDoc
     */
    public function provides(): array
    {
        return []; // TODO Improve performance
//        $ids = $this->fetchIds();
//
//        return [
//            BlueprintType::Plugin->value => array_map(fn (int $id) => "spiget/{$id}", $ids),
//        ];
    }
}
