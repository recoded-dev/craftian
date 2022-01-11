<?php

namespace Recoded\Craftian;

use Recoded\Craftian\Console\Commands\InitCommand;
use Recoded\Craftian\Console\ProgressBarFormat;
use Recoded\Craftian\Repositories\BukkitRepository;
use Recoded\Craftian\Repositories\BungeecordRepository;
use Recoded\Craftian\Repositories\PaperRepository;
use Recoded\Craftian\Repositories\SnapshotRepository;
use Recoded\Craftian\Repositories\SpigotRepository;
use Recoded\Craftian\Repositories\VanillaRepository;
use Recoded\Craftian\Repositories\WaterfallRepository;
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
            'Downloading [%downloadable%] %current%/%max% [%bar%] %percent:3s%%',
        );

        ProgressBar::setFormatDefinition(
            ProgressBarFormat::DOWNLOADING_VERSION->value,
            'Downloading [%downloadable% (%version%)] %current%/%max% [%bar%] %percent:3s%%',
        );

        $this->add(new InitCommand());
    }

    public static function bootCwd(): void
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
        ];
    }

    public static function getCwd(): string
    {
        return static::$cwd;
    }
}
