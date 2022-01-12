<?php

namespace Recoded\Craftian\Contracts;

interface SoftwareConstraints
{
    /**
     * Constraint of the server version.
     *
     * @return string
     */
    public function getSoftwareConstraint(): string;
}
