<?php

namespace Recoded\Craftian\Contracts;

use Recoded\Craftian\Configuration\BlueprintType;
use Recoded\Craftian\Configuration\ChecksumType;

interface Installable extends Downloadable, Version
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
     * Type of the installable.
     *
     * @return \Recoded\Craftian\Configuration\BlueprintType
     */
    public function getType(): BlueprintType;

    /**
     * Name to install the artifact as in the installation location.
     *
     * @return string
     */
    public function installationFilename(): string;

    /**
     * Directory to install the artifact to.
     *
     * @return string
     */
    public function installationLocation(): string;
}
