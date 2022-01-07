<?php

namespace Recoded\Craftian;

use Recoded\Craftian\Console\Commands\InitCommand;
use Recoded\Craftian\Console\ProgressBarFormat;
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
            throw new \Exception('Couldn\'t determine current directory, this is needed for Craftian');
        }

        static::$cwd = $cwd;
    }

    public static function getCwd(): string
    {
        return static::$cwd;
    }
}
