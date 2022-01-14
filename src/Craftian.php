<?php

namespace Recoded\Craftian;

use Recoded\Craftian\Console\Commands\InitCommand;
use Recoded\Craftian\Console\Commands\InstallCommand;
use Recoded\Craftian\Console\ProgressBarFormat;
use Recoded\Craftian\Repositories\Software\BukkitRepository;
use Recoded\Craftian\Repositories\Software\PaperRepository;
use Recoded\Craftian\Repositories\Software\Proxies\BungeecordRepository;
use Recoded\Craftian\Repositories\Software\Proxies\WaterfallRepository;
use Recoded\Craftian\Repositories\Software\SnapshotRepository;
use Recoded\Craftian\Repositories\Software\SpigotRepository;
use Recoded\Craftian\Repositories\Software\VanillaRepository;
use Recoded\Craftian\Repositories\SpigetRepository;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\ProgressBar;

class Craftian extends Application
{
    protected static string $cwd;

    public function boot(): void
    {
        static::bootCwd();

        ProgressBar::setFormatDefinition(
            ProgressBarFormat::DOWNLOADING->value,
            'Downloading: %downloadable:-40s% [%bar%] %percent:3s%% %current%MB / %max%MB',
        );

        $this->add(new InitCommand());
        $this->add(new InstallCommand());
    }

    protected static function bootCwd(): void
    {
        $cwd = getcwd();

        if ($cwd === false) {
            throw new \Exception('Couldn\'t determine current directory, this is however needed for Craftian');
        }

        static::$cwd = $cwd;
    }

    /**
     * @return array<\Recoded\Craftian\Contracts\Repository>
     */
    public static function defaultRepositories(): array
    {
        return [
            new VanillaRepository(),
            new SpigotRepository(),
            new BukkitRepository(),
            new PaperRepository(),
            new BungeecordRepository(),
            new WaterfallRepository(),
            new SnapshotRepository(),

            new SpigetRepository(),
        ];
    }

    public static function getCwd(): string
    {
        return static::$cwd;
    }
}
