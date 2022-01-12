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

    public function __construct(array $config)
    {
        $this->hydrateConfigurations($config['configurations']);
    }

    /**
     * @inheritDoc
     */
    public function autocomplete(string $query): array
    {
        return array_filter(
            $this->configurations,
            fn (Installable $installable) => str_starts_with($installable->getName(), $query),
        );
    }

    /**
     * @inheritDoc
     */
    public function get(string $name): array
    {
        return array_filter($this->configurations, fn (Installable $installable) => $installable->getName() === $name);
    }

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
            return array_unique(
                array_map(fn (Installable $installable) => $installable->getName(), $configurations),
            );
        }, $provides);
    }
}
