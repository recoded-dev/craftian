<?php

namespace Recoded\Craftian\Configuration;

enum ChecksumType: string
{
    case None = 'none';
    case Sha256 = 'sha256';
}
