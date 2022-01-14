<?php

namespace Recoded\Craftian\Configuration;

abstract class Blueprint implements \JsonSerializable
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
     * @param array<string, mixed> $blueprint
     * @return self
     */
    public static function fromArray(array $blueprint): self
    {
        if (
            !isset($blueprint['type'])
            || !is_string($blueprint['type'])
            || BlueprintType::tryFrom($blueprint['type']) === null
        ) {
            throw new \InvalidArgumentException('Unknown blueprint type');
        }

        return BlueprintType::from($blueprint['type'])->initialize($blueprint);
    }

    public function getType(): BlueprintType
    {
        return BlueprintType::fromBlueprint($this);
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
