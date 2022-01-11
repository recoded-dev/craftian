<?php

namespace Recoded\Craftian\Repositories;

class VanillaRepository extends ServerJarsRepository
{
    public static function getName(): string
    {
        return 'minecraft/server';
    }

    protected function type(): string
    {
        return 'vanilla';
    }
}
