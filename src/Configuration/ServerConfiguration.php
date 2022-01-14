<?php

namespace Recoded\Craftian\Configuration;

use Recoded\Craftian\Configuration\Locking\Locker;
use Recoded\Craftian\Contracts\Requirements;

class ServerConfiguration extends Configuration implements Requirements
{
    protected bool $defaultRepositories = true;

    /**
     * @var array<array-key, array<string, mixed>>
     */
    protected array $repositories = [];

    /**
     * @var array<string, string>
     */
    protected array $requirements = [];

    protected function createLock(): LockedConfiguration
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

    public function lock(): LockedConfiguration
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

    public function requirements(): array
    {
        return $this->requirements;
    }

    public function toArray(): array
    {
        return [
            'require' => $this->requirements,
            'enable-default-repositories' => $this->defaultRepositories,
        ];
    }
}
