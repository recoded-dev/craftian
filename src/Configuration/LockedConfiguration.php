<?php

namespace Recoded\Craftian\Configuration;

class LockedConfiguration implements \JsonSerializable
{
    /**
     * @param array<\Recoded\Craftian\Configuration\Configuration> $requirements
     */
    public function __construct(
        protected array $requirements = [],
    ) {
        //
    }

    /**
     * @return array<\Recoded\Craftian\Configuration\Configuration>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @return array<\Recoded\Craftian\Configuration\Configuration>
     */
    public function toArray(): array
    {
        return $this->requirements;
    }
}
