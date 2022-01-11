<?php

namespace Recoded\Craftian\Repositories;

class BungeecordRepository extends ServerJarsRepository
{
    public static function getName(): string
    {
        return 'spigotmc/bungeecord';
    }

    protected function type(): string
    {
        return 'bungeecord';
    }
}
