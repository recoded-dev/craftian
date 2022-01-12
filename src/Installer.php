<?php

namespace Recoded\Craftian;

use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\RequestOptions;
use Recoded\Craftian\Configuration\Configuration;
use Recoded\Craftian\Contracts\Installable;
use Recoded\Craftian\Http\Client;

class Installer
{
    public function install(
        Configuration&Installable $installable,
        ?Client $client = null,
        ?callable $progress = null,
    ): PromiseInterface {
        $client ??= new Client();

//        $directory = Craftian::getCwd() . '/plugins';
        $directory = Craftian::getCwd();
        !file_exists($directory) && mkdir($directory, recursive: true);

        return $client->getAsync($installable->getURL(), [
            RequestOptions::SINK => sprintf(
                '%s/%s.jar',
                $directory,
                str_replace(['/', '\\'], '-', $installable->getName()),
            ), // TODO where to generate this?
            RequestOptions::PROGRESS => $progress,
        ]);
    }
}
