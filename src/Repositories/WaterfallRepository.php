<?php

namespace Recoded\Craftian\Repositories;

class WaterfallRepository extends PaperMcRepository
{
    public static function getName(): string
    {
        return 'papermc/waterfall';
    }

    protected function project(): string
    {
        return 'waterfall';
    }
}
