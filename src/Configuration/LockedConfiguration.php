<?php

namespace Recoded\Craftian\Configuration;

class LockedConfiguration
{
    public function __construct(
        protected array $requirements = []
    ) { }
}
