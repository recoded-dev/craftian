<?php

namespace Recoded\Craftian\Contracts;

use Recoded\Craftian\Configuration\ChecksumType;

interface Installable extends Downloadable
{
    /**
     * Checksum type of the artifact.
     *
     * @return \Recoded\Craftian\Configuration\ChecksumType
     */
    public function checksumType(): ChecksumType;

    /**
     * Checksum to compare the downloaded artifact against.
     *
     * @return string|null
     */
    public function getChecksum(): ?string;

    /**
     * Name of the installable.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Version of the installable.
     *
     * @return string
     */
    public function getVersion(): string;
}
