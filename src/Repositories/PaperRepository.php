<?php

namespace Recoded\Craftian\Repositories;

class PaperRepository extends PaperMcRepository
{
    public static function getName(): string
    {
        return 'papermc/paper';
    }

    protected function project(): string
    {
        return 'paper';
    }
}
