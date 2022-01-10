<?php

namespace Recoded\Craftian\Configuration;

class ServerConfiguration extends Configuration
{
    protected bool $defaultRepositories;
    protected array $requirements;

    protected function createLock(): LockedConfiguration
    {
        return new LockedConfiguration($this->requirements);
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
        $this->requirements = $config['requirements'] ?? [];
    }

    public function lock(): LockedConfiguration
    {
        return $this->lock ??= $this->createLock();
    }

    public function repositories(): array
    {
        return [];
//        return $this->repositories;
    }

    public function toArray(): array
    {
        return [
            'requirements' => $this->requirements,
            'enable-default-repositories' => $this->defaultRepositories,
        ];
    }
}