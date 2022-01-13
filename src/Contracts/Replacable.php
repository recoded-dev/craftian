<?php

namespace Recoded\Craftian\Contracts;

interface Replacable extends Installable
{
    /**
     * Array with configurations this configuration replaces.
     *
     * @return array<string, string>
     */
    public function replaces(): array;
}
