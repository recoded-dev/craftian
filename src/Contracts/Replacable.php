<?php

namespace Recoded\Craftian\Contracts;

interface Replacable extends Installable
{
    /**
     * Array with blueprints this blueprint replaces.
     *
     * @return array<string, string>
     */
    public function replaces(): array;
}
