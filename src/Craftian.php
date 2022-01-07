<?php

namespace Recoded\Craftian;

use Recoded\Craftian\Console\Commands\InitCommand;
use Symfony\Component\Console\Application;

class Craftian extends Application
{
    public function boot(): void
    {
        $this->add(new InitCommand());
    }
}
