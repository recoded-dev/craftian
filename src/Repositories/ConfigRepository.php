<?php

namespace Recoded\Craftian\Repositories;

use Recoded\Craftian\Configuration\Blueprint;
use Recoded\Craftian\Contracts\Installable;
use Recoded\Craftian\Contracts\Repository;

class ConfigRepository implements Repository
{
    /**
     * @var array<\Recoded\Craftian\Configuration\Blueprint&\Recoded\Craftian\Contracts\Installable>
     */
    protected array $blueprints;

    /**
     * @param array<string, mixed> $config
     * @throws \Exception
     */
    public function __construct(array $config)
    {
        if (!is_array($blueprints = $config['blueprints'] ?? [])) {
            throw new \Exception('Invalid blueprints section for config repository');
        }

        $this->hydrateBlueprints($blueprints);
    }

    /**
     * @inheritDoc
     */
    public function autocomplete(string $query): array
    {
        return array_map(
            fn (Blueprint&Installable $blueprint) => $blueprint->getName(),
            array_filter(
                $this->blueprints,
                fn (Blueprint&Installable $blueprint) => str_starts_with($blueprint->getName(), $query),
            ),
        );
    }

    /**
     * @inheritDoc
     */
    public function get(string $name): array
    {
        return array_filter(
            $this->blueprints,
            fn (Blueprint&Installable $blueprint) => $blueprint->getName() === $name,
        );
    }

    /**
     * @param array<array-key, array<string, mixed>> $configurations
     */
    protected function hydrateBlueprints(array $configurations): void
    {
        $this->blueprints = array_filter(
            array_map([Blueprint::class, 'fromArray'], $configurations),
            fn (Blueprint $blueprint) => $blueprint instanceof Installable,
        );
    }

    /**
     * @inheritDoc
     */
    public function provides(): array
    {
        $provides = [];

        foreach ($this->blueprints as $blueprint) {
            $provides[$blueprint->getType()->value][] = $blueprint;
        }

        return array_map(function (array $blueprints) {
            return array_values(array_unique(
                array_map(fn (Installable $installable) => $installable->getName(), $blueprints),
            ));
        }, $provides);
    }
}
