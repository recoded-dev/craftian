<?php

namespace Recoded\Craftian\Configuration;

use Recoded\Craftian\Contracts\Installable;

class SoftwareConfiguration extends Configuration implements Installable
{
    protected ?string $checksum;
    protected string $checksumType;
    protected string $name;
    protected string $url;
    protected string $version;

    public function getChecksum(): ?string
    {
        return $this->checksum;
    }

    public function checksumType(): ChecksumType
    {
        return ChecksumType::from($this->checksumType);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getURL(): string
    {
        return $this->url;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function initialize(array $config): void
    {
        $this->checksum = $config['distribution']['checksum'] ?? null;
        $this->checksumType = $config['distribution']['checksum-type'] ?? ChecksumType::None->value;
        $this->name = $config['name'];
        $this->url = $config['distribution']['url'];
        $this->version = $config['version'];
    }

    public function toArray(): array
    {
        return [
            'distribution' => [
                'checksum' => $this->checksum,
                'checksum-type' => $this->checksumType ?? 'none',
                'url' => $this->url,
            ],
            'name' => $this->name,
            'version' => $this->version,
        ];
    }
}
