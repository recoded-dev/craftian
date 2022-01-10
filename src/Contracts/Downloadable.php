<?php

namespace Recoded\Craftian\Contracts;

interface Downloadable
{
    // public function getDownloadStrategy(): enum; TODO local file or HTTP download

    public function getURL(): string;
}
