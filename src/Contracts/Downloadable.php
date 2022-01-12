<?php

namespace Recoded\Craftian\Contracts;

interface Downloadable
{
    // public function getDownloadStrategy(): enum; TODO local file or HTTP download

    /**
     * The URL used to download the artifact.
     *
     * @return string
     */
    public function getURL(): string;
}
