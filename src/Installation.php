<?php

namespace Recoded\Craftian;

use Recoded\Craftian\Configuration\Configuration;
use Recoded\Craftian\Contracts\Installable;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class Installation
{
    public ProgressBar $progressBar;
    public OutputInterface $output;

    public function __construct(
        public readonly Configuration&Installable $installable,
    ) {
    }
}
