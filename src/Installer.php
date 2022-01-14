<?php

namespace Recoded\Craftian;

use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\RequestOptions;
use Recoded\Craftian\Configuration\Configuration;
use Recoded\Craftian\Configuration\ServerConfiguration;
use Recoded\Craftian\Contracts\Installable;
use Recoded\Craftian\Http\Client;

class Installer
{
    public readonly Client $client;
    public readonly InstallManifest $manifest;

    public function __construct(
        public readonly ServerConfiguration $serverConfiguration,
        ?Client $client = null,
    ) {
        $this->client = $client ?? new Client();
        $this->manifest = InstallManifest::fromLock($this);
    }

    public function install(
        Configuration&Installable $installable,
        ?callable $progress = null,
    ): PromiseInterface {
        // TODO
//        $directory = Craftian::getCwd() . '/plugins';
        $directory = Craftian::getCwd();
        !file_exists($directory) && mkdir($directory, recursive: true);

        return $this->client->getAsync($installable->getURL(), [
            RequestOptions::SINK => sprintf(
                '%s/%s.jar',
                $directory,
                str_replace(['/', '\\'], '-', $installable->getName()),
            ),
            RequestOptions::PROGRESS => $progress,
        ]);
    }
}
