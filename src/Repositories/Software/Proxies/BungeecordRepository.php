<?php

namespace Recoded\Craftian\Repositories\Software\Proxies;

use Recoded\Craftian\Repositories\Software\ServerJarsRepository;

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
