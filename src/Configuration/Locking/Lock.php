<?php

namespace Recoded\Craftian\Configuration\Locking;

/**
 * @implements \IteratorAggregate<\Recoded\Craftian\Configuration\Blueprint&\Recoded\Craftian\Contracts\Installable>
 */
class Lock implements \IteratorAggregate, \JsonSerializable
{
    /**
     * @param array<\Recoded\Craftian\Configuration\Blueprint&\Recoded\Craftian\Contracts\Installable> $requirements
     */
    public function __construct(
        public readonly array $requirements = [],
    ) {
    }

    /**
     * @return \ArrayIterator<array-key, \Recoded\Craftian\Configuration\Blueprint&\Recoded\Craftian\Contracts\Installable>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->requirements);
    }

    /**
     * @return array<\Recoded\Craftian\Configuration\Blueprint>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @return array<\Recoded\Craftian\Configuration\Blueprint>
     */
    public function toArray(): array
    {
        return $this->requirements;
    }
}
