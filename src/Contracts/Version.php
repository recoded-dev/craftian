<?php

namespace Recoded\Craftian\Contracts;

interface Version
{
    /**
     * Version of the artifact.
     *
     * @return string
     */
    public function getVersion(): string;
}
