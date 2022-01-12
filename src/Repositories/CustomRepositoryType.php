<?php

namespace Recoded\Craftian\Repositories;

use Recoded\Craftian\Contracts\Repository;

enum CustomRepositoryType: string
{
    case Config = 'config';

    /**
     * @param array<string, mixed> $config
     * @return \Recoded\Craftian\Contracts\Repository
     */
    public function initialize(array $config): Repository
    {
        return new (match ($this) {
            self::Config => ConfigRepository::class,
        })($config);
    }
}
