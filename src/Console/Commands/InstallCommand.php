<?php

namespace Recoded\Craftian\Console\Commands;

use Recoded\Craftian\InstallableProgressBarUpdater;
use Recoded\Craftian\Installer;
use Recoded\Craftian\Repositories\Software\VanillaRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InstallCommand extends Command
{
    protected static $defaultDescription = 'Install required dependencies';
    protected static $defaultName = 'install';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $bar = new ProgressBar($output);

        $installing = (new VanillaRepository())->get('minecraft/server')[0];

        (new Installer())
            ->install($installing, new InstallableProgressBarUpdater($installing, $bar))
            ->wait();

        return static::SUCCESS;
    }
}
