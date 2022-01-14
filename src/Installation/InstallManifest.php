<?php

namespace Recoded\Craftian\Installation;

use Recoded\Craftian\Configuration\Blueprint;
use Recoded\Craftian\Contracts\Installable;

/**
 * @implements \IteratorAggregate<\Recoded\Craftian\Installation\Installation>
 */
class InstallManifest implements \IteratorAggregate
{
    /**
     * @var array<\Recoded\Craftian\Installation\Installation>
     */
    protected array $toInstall = [];

    final private function __construct(
        protected Installer $installer,
    ) {
    }

    public static function fromLock(Installer $installer): static
    {
        $manifest = new static($installer);

        foreach ($installer->server->lock() as $blueprint) {
            $manifest->markForInstallation($blueprint);
        }

        return $manifest;
    }

    /**
     * @return \ArrayIterator<array-key, \Recoded\Craftian\Installation\Installation>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->toInstall);
    }

    /**
     * @param callable|null $onProgress
     * @return array<\GuzzleHttp\Promise\PromiseInterface>
     */
    public function install(?callable $onProgress = null): array
    {
        return array_map(function (Installation $installation) use ($onProgress) {
            return $this->installer->install(
                $installation->installable,
                $onProgress !== null ? $onProgress($installation) : null,
            );
        }, $this->toInstall);
    }

    protected function markForInstallation(Blueprint&Installable $blueprint): void
    {
        $path = $this->installer->getInstallPath($blueprint);

        $verificationPasses = file_exists($path) && $blueprint->checksumType()->verifyFile(
            $path,
            $blueprint->getChecksum(),
        );

        if ($verificationPasses) {
            return;
        }

        $this->toInstall[] = new Installation($blueprint);
    }
}
