<?php

namespace Recoded\Craftian\Configuration;

enum ChecksumType: string
{
    case None = 'none';
    case Md5 = 'md5';
    case Sha256 = 'sha256';
}
