<?php

namespace Recoded\Craftian\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends Command
{
    protected static $defaultDescription = 'Boilerplate a server';
    protected static $defaultName = 'init';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return static::SUCCESS;
    }
}
