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
    protected static string $configPath;
    protected static string $cwd;
    /**
     * @var array<string, string>
     */
    protected static array $authenticationConfig;

    public function boot(): void
    {
        $userId = fileowner(__FILE__);

        if ($userId === false) {
            throw new \Exception('Cannot locate global config for Craftian');
        }

        $userData = posix_getpwuid($userId);

        if ($userData === false) {
            throw new \Exception('Cannot locate global config for Craftian');
        }

        static::$configPath = $userData['dir'] . '/.config/craftian';

        static::bootCwd();
        static::bootHttpConfig();

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

    protected static function bootHttpConfig(): void
    {
        $resource = @fopen(static::$configPath . '/auth.json', 'r');

        if ($resource === false) {
            static::$authenticationConfig = [];
            return;
        }

        $contents = stream_get_contents($resource);

        if ($contents === false) {
            static::$authenticationConfig = [];
            return;
        }

        $data = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);

        if ($data === false || !is_array($data)) {
            static::$authenticationConfig = [];
            return;
        }

        static::$authenticationConfig = $data;
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

    /**
     * @return array<string, mixed>
     */
    public static function httpConfig(): array
    {
        return [
            'authentication' => static::$authenticationConfig,
        ];
    }
}
