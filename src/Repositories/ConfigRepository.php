<?php

namespace Recoded\Craftian\Repositories;

use Recoded\Craftian\Configuration\Configuration;
use Recoded\Craftian\Contracts\Installable;
use Recoded\Craftian\Contracts\Repository;

class ConfigRepository implements Repository
{
    /**
     * @var array<\Recoded\Craftian\Configuration\Configuration&\Recoded\Craftian\Contracts\Installable>
     */
    protected array $configurations;

    /**
     * @param array<string, mixed> $config
     * @throws \Exception
     */
    public function __construct(array $config)
    {
        if (!is_array($configurations = $config['configurations'] ?? [])) {
            throw new \Exception('Invalid configurations section for config repository');
        }

        $this->hydrateConfigurations($configurations);
    }

    /**
     * @inheritDoc
     */
    public function autocomplete(string $query): array
    {
        return array_map(
            fn (Installable $installable) => $installable->getName(),
            array_filter(
                $this->configurations,
                fn (Installable $installable) => str_starts_with($installable->getName(), $query),
            ),
        );
    }

    /**
     * @inheritDoc
     */
    public function get(string $name): array
    {
        return array_filter($this->configurations, fn (Installable $installable) => $installable->getName() === $name);
    }

    /**
     * @param array<array-key, array<string, mixed>> $configurations
     */
    protected function hydrateConfigurations(array $configurations): void
    {
        $this->configurations = array_filter(
            array_map([Configuration::class, 'fromArray'], $configurations),
            fn (Configuration $configuration) => $configuration instanceof Installable,
        );
    }

    /**
     * @inheritDoc
     */
    public function provides(): array
    {
        $provides = [];

        foreach ($this->configurations as $configuration) {
            $provides[$configuration->getType()->value][] = $configuration;
        }

        return array_map(function (array $configurations) {
            return array_values(array_unique(
                array_map(fn (Installable $installable) => $installable->getName(), $configurations),
            ));
        }, $provides);
    }
}
