<?php

namespace Recoded\Craftian;

/**
 * @implements \IteratorAggregate<\Recoded\Craftian\Installation>
 */
class InstallManifest implements \IteratorAggregate
{
    /**
     * @var array<\Recoded\Craftian\Installation>
     */
    protected array $toInstall = []; // TODO build this

    final private function __construct(
        protected Installer $installer,
    ) {
    }

    public static function fromLock(Installer $installer): static
    {
        $manifest = new static($installer);

        foreach ($installer->serverConfiguration->lock() as $configuration) {
            $manifest->toInstall[] = new Installation($configuration);
        }

        return $manifest;
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

    /**
     * @return \ArrayIterator<array-key, \Recoded\Craftian\Installation>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->toInstall);
    }
}
