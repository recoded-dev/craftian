<?php

namespace Recoded\Craftian\Console\Commands;

use GuzzleHttp\Promise\EachPromise;
use Recoded\Craftian\Configuration\ServerLoader;
use Recoded\Craftian\Console\ProgressBarFormat;
use Recoded\Craftian\Craftian;
use Recoded\Craftian\Installation\InstallableProgressBarUpdater;
use Recoded\Craftian\Installation\Installation;
use Recoded\Craftian\Installation\Installer;
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
            $server = (new ServerLoader())->load(),
        );

        /** @var \Recoded\Craftian\Installation\Installation $installation */
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

        (new EachPromise($promises, [
            'concurrency' => 10,
            'rejected' => function (\Throwable $throwable) {
                throw $throwable;
            },
        ]))->promise()->then(function () use ($server) {
            $lockPath = Craftian::getCwd() . '/craftian.lock';

            if (file_exists($lockPath)) {
                return;
            }

            $resource = fopen($lockPath, 'a');

            if ($resource === false) {
                throw new \Exception('Cannot read/write "craftian.lock" file');
            }

            $lockJson = json_encode($server->lock(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

            if ($lockJson === false) {
                throw new \Exception('Cannot JSON encode lock file');
            }

            fwrite($resource, $lockJson);
            fclose($resource);
        })->wait();

        return static::SUCCESS;
    }
}
