<?php

namespace Recoded\Craftian\Repositories\Software;

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
