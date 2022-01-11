<?php

namespace Recoded\Craftian\Repositories;

class BukkitRepository extends ServerJarsRepository
{
    public static function getName(): string
    {
        return 'bukkit/server';
    }

    protected function type(): string
    {
        return 'bukkit';
    }
}
