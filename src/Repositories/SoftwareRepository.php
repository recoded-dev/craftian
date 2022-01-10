<?php

namespace Recoded\Craftian\Repositories;

use Recoded\Craftian\Configuration\ConfigurationType;
use Recoded\Craftian\Contracts\Repository;

abstract class SoftwareRepository implements Repository
{
    /**
     * @inheritDoc
     */
    public function autocomplete(string $query): array
    {
        return str_starts_with(static::getName(), $query)
            ? [static::getName()]
            : [];
    }

    /**
     * @inheritDoc
     */
    public function provides(): array
    {
        return [
            ConfigurationType::Software->value => [static::getName()],
        ];
    }

    abstract public static function getName(): string;
}
