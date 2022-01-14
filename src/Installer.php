<?php

namespace Recoded\Craftian;

use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\RequestOptions;
use Recoded\Craftian\Configuration\Blueprint;
use Recoded\Craftian\Configuration\ServerBlueprint;
use Recoded\Craftian\Contracts\Installable;
use Recoded\Craftian\Http\Client;

class Installer
{
    public readonly Client $client;
    public readonly InstallManifest $manifest;

    public function __construct(
        public readonly ServerBlueprint $server,
        ?Client $client = null,
    ) {
        $this->client = $client ?? new Client();
        $this->manifest = InstallManifest::fromLock($this);
    }

    public function install(
        Blueprint&Installable $installable,
        ?callable $progress = null,
    ): PromiseInterface {
        $directory = rtrim(Craftian::getCwd() . $installable->installationLocation(), DIRECTORY_SEPARATOR);
        $path = $directory . DIRECTORY_SEPARATOR . $installable->installationFilename();

        if (!is_dir($directory)) {
            mkdir($directory, recursive: true);
        }

        $url = $installable->getURL();

        if (str_starts_with($url, 'http')) {
            return $this->promiseDownload($url, $path, $progress);
        } else {
            return $this->promiseSymlink($url, $path, $progress);
        }
    }

    protected function promiseDownload(string $url, string $path, ?callable $progress): PromiseInterface
    {
        return $this->client->requestAsync('GET', $url, [
            RequestOptions::SINK => $path,
            RequestOptions::PROGRESS => $progress,
        ]);
    }

    protected function promiseSymlink(string $url, string $path, ?callable $progress): PromiseInterface
    {
        $promise = new Promise(function () use ($path, &$promise, $url) {
            /** @var \GuzzleHttp\Promise\PromiseInterface $promise */
            symlink($url, $path)
                ? $promise->resolve(null)
                : $promise->reject(null);
        });

        $promise->then(function () use ($progress) {
            if ($progress !== null) {
                $progress(1.0 * 1024 * 1024, 1.0 * 1024 * 1024, .0, .0);
            }
        }, function () use ($progress) {
            if ($progress !== null) {
                $progress(1.0 * 1024 * 1024, .0, .0, .0);
            }
        });

        return $promise;
    }
}
