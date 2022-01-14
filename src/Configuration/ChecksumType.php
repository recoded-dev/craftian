<?php

namespace Recoded\Craftian\Configuration;

enum ChecksumType: string
{
    case None = 'none';
    case Md5 = 'md5';
    case Sha256 = 'sha256';

    public function verifyFile(string $filename, ?string $checksum): bool
    {
        if ($this === self::None) {
            return $checksum === null;
        }

        return hash_file($this->value, $filename) === $checksum;
    }
}
