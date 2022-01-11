<?php

namespace Recoded\Craftian\Repositories;

class SpigotRepository extends ServerJarsRepository
{
    public static function getName(): string
    {
        return 'spigotmc/spigot';
    }

    protected function type(): string
    {
        return 'spigot';
    }
}
