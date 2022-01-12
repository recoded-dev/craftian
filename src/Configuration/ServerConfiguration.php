<?php

namespace Recoded\Craftian\Configuration;

use Recoded\Craftian\Contracts\Requirements;

class ServerConfiguration extends Configuration implements Requirements
{
    protected bool $defaultRepositories;

    /**
     * @var array<array-key, array<string, mixed>>
     */
    protected array $repositories;

    /**
     * @var array<string, string>
     */
    protected array $requirements;

    protected function createLock(): LockedConfiguration
    {
//        return new LockedConfiguration($this->requirements);
        return new LockedConfiguration([]);
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
        $this->defaultRepositories = $config['enable-default-repositories'];
        $this->repositories = $config['repositories'] ?? [];
        $this->requirements = $config['require'] ?? [];
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
            'requirements' => $this->requirements,
            'enable-default-repositories' => $this->defaultRepositories,
        ];
    }
}
