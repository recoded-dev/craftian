<?php

namespace Recoded\Craftian\Configuration;

class LockedConfiguration
{
    /**
     * @param array<\Recoded\Craftian\Configuration\Configuration> $requirements
     */
    public function __construct(
        protected array $requirements = [],
    ) {
        //
    }
}
