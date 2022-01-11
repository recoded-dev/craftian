<?php

namespace Recoded\Craftian\Configuration;

// TODO come up with better umbrella name for server, software and plugin (and potentially more in the future)
// Maybe: Blueprint, Composition, Project
abstract class Configuration implements \JsonSerializable
{
    /**
     * @var array<string, mixed>
     */
    protected array $config;

    /**
     * @param array<string, mixed> $config
     */
    final public function __construct(array $config)
    {
        $this->initialize(
            $this->config = array_merge($this->defaultConfig(), $config),
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultConfig(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    final public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @param array<string, mixed> $config
     */
    abstract public function initialize(array $config): void;

    /**
     * @return array<string, mixed>
     */
    abstract public function toArray(): array;
}
