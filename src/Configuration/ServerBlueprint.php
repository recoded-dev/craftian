<?php

namespace Recoded\Craftian\Configuration;

use Recoded\Craftian\Configuration\Locking\Lock;
use Recoded\Craftian\Configuration\Locking\Locker;
use Recoded\Craftian\Contracts\Locking;
use Recoded\Craftian\Contracts\Requirements;

class ServerBlueprint extends Blueprint implements Locking, Requirements
{
    protected bool $defaultRepositories = true;
    protected Lock $lock;

    /**
     * @var array<array-key, array<string, mixed>>
     */
    protected array $repositories = [];

    /**
     * @var array<string, string>
     */
    protected array $requirements = [];

    protected function createLock(): Lock
    {
        return (new Locker($this))->lock();
    }

    protected function defaultConfig(): array
    {
        return [
            'enable-default-repositories' => true,
        ];
    }

    public function hasDefaultRepositories(): bool
    {
        return $this->defaultRepositories;
    }

    public function initialize(array $config): void
    {
        if (isset($config['enable-default-repositories']) && is_bool($config['enable-default-repositories'])) {
            $this->defaultRepositories = $config['enable-default-repositories'];
        }

        if (isset($config['repositories']) && is_array($config['repositories'])) {
            $this->repositories = $config['repositories'];
        }

        if (isset($config['require']) && is_array($config['require'])) {
            $this->requirements = $config['require'];
        }
    }

    /**
     * @inheritDoc
     */
    public function lock(): Lock
    {
        return $this->lock ??= $this->createLock();
    }

    /**
     * @return array<array-key, array<string, mixed>>
     */
    public function repositories(): array
    {
        return $this->repositories;
    }

    /**
     * @inheritDoc
     */
    public function requirements(): array
    {
        return $this->requirements;
    }

    /**
     * @inheritDoc
     */
    public function setLock(Lock $lock): void
    {
        $this->lock = $lock;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->getType()->value,
            'require' => $this->requirements,
            'enable-default-repositories' => $this->defaultRepositories,
        ];
    }
}
