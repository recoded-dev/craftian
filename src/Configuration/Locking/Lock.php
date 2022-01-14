<?php

namespace Recoded\Craftian\Configuration\Locking;

use Recoded\Craftian\Configuration\Blueprint;

/**
 * @implements \IteratorAggregate<\Recoded\Craftian\Configuration\Blueprint&\Recoded\Craftian\Contracts\Installable>
 */
class Lock implements \IteratorAggregate, \JsonSerializable
{
    /**
     * @param array<\Recoded\Craftian\Configuration\Blueprint&\Recoded\Craftian\Contracts\Installable> $requirements
     */
    final public function __construct(
        public readonly array $requirements,
        public readonly int $time,
        public readonly string $checksum,
    ) {
    }

    /**
     * @param array{requirements: array<array<string, mixed>>, time: int, checksum: string} $configuration
     * @return static
     */
    public static function fromArray(array $configuration): static
    {
        /** @var array<\Recoded\Craftian\Configuration\Blueprint&\Recoded\Craftian\Contracts\Installable> $requirements */
        $requirements = array_map([Blueprint::class, 'fromArray'], $configuration['requirements']);

        return new static($requirements, $configuration['time'], $configuration['checksum']);
    }

    /**
     * @return \ArrayIterator<array-key, \Recoded\Craftian\Configuration\Blueprint&\Recoded\Craftian\Contracts\Installable>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->requirements);
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'checksum' => $this->checksum,
            'time' => $this->time,
            'requirements' => $this->requirements,
        ];
    }
}
