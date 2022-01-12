<?php

namespace Recoded\Craftian\Console\Commands;

use GuzzleHttp\Promise\Utils;
use Recoded\Craftian\Http\Client;
use Recoded\Craftian\InstallableProgressBarUpdater;
use Recoded\Craftian\Installer;
use Recoded\Craftian\Repositories\RepositoryManager;
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
        $installer = new Installer();
        $promises = [];

        $repositoryManager = RepositoryManager::default();
        $client = new Client();

        foreach (['minecraft/server', 'spigotmc/bungeecord'] as $installing) {
            $section = $output->section();
            $bar = new ProgressBar($section);

            /** @var \Recoded\Craftian\Configuration\Configuration&\Recoded\Craftian\Contracts\Installable $installable */
            $installable = $repositoryManager->get($installing)[0];

            $updater = new InstallableProgressBarUpdater($installable, $bar);

            $promises[] = $installer->install($installable, $client, $updater);
        }

        Utils::settle($promises)->wait();

        return static::SUCCESS;
    }
}
