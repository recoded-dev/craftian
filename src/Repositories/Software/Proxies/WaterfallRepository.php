<?php

namespace Recoded\Craftian\Repositories\Software\Proxies;

use Recoded\Craftian\Repositories\Software\PaperMcRepository;

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
