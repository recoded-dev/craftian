<?php

namespace Recoded\Craftian\Repositories;

class SnapshotRepository extends ServerJarsRepository
{
    public static function getName(): string
    {
        return 'minecraft/snapshot';
    }

    protected function type(): string
    {
        return 'snapshot';
    }
}
