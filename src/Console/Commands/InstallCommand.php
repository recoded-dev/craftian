<?php

namespace Recoded\Craftian\Console\Commands;

use GuzzleHttp\Promise\Utils;
use Recoded\Craftian\Configuration\ServerLoader;
use Recoded\Craftian\Console\ProgressBarFormat;
use Recoded\Craftian\InstallableProgressBarUpdater;
use Recoded\Craftian\Installation;
use Recoded\Craftian\Installer;
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
        /** @var \Symfony\Component\Console\Output\ConsoleOutput $output */

        $installer = new Installer(
            (new ServerLoader())->load(),
        );

        /** @var \Recoded\Craftian\Installation $installation */
        foreach ($installer->manifest as $installation) {
            $section = $output->section();
            $installation->progressBar = new ProgressBar($section);
            $installation->progressBar->setFormat(ProgressBarFormat::DOWNLOADING->value);
            $installation->output = $section;
        }

        $promises = $installer->manifest->install(fn (Installation $installation) => new InstallableProgressBarUpdater(
            $installation->installable,
            $installation->progressBar,
        ));

        Utils::settle($promises)->wait();

        return static::SUCCESS;
    }
}
