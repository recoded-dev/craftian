<?php

namespace Recoded\Craftian\Configuration;

// TODO come up with better umbrella name for server, software and plugin (and potentially more in the future)
abstract class Configuration implements \JsonSerializable
{
    protected array $config;

    final public function __construct(array $config) {
        $this->initialize(
            $this->config = array_merge($this->defaultConfig(), $config),
        );
    }

    protected function defaultConfig(): array
    {
        return [];
    }

    final public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    abstract public function initialize(array $config): void;

    abstract public function toArray(): array;
}
