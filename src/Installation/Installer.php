<?php

namespace Recoded\Craftian\Installation;

use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\RequestOptions;
use Recoded\Craftian\Configuration\Blueprint;
use Recoded\Craftian\Configuration\ServerBlueprint;
use Recoded\Craftian\Contracts\Installable;
use Recoded\Craftian\Craftian;
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

    public function getInstallPath(Blueprint&Installable $blueprint): string
    {
        return sprintf(
            '%s%s%s%s',
            Craftian::getCwd(),
            rtrim($blueprint->installationLocation(), DIRECTORY_SEPARATOR),
            DIRECTORY_SEPARATOR,
            $blueprint->installationFilename(),
        );
    }

    public function install(
        Blueprint&Installable $blueprint,
        ?callable $progress = null,
    ): PromiseInterface {
        $directory = dirname(
            $path = $this->getInstallPath($blueprint),
        );

        if (!is_dir($directory)) {
            mkdir($directory, recursive: true);
        }

        $url = $blueprint->getURL();

        $promise = str_starts_with($url, 'http')
            ? $this->promiseDownload($url, $path, $progress)
            : $this->promiseSymlink($url, $path, $progress);

        return $promise->then(function () use ($blueprint, $path) {
            if (!$blueprint->checksumType()->verifyFile($path, $blueprint->getChecksum())) {
                throw new \Exception('Hash verification failed for requirement ' . $blueprint->getName());
            }
        });
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
