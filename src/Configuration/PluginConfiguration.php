<?php

namespace Recoded\Craftian\Configuration;

use Recoded\Craftian\Contracts\Installable;
use Recoded\Craftian\Contracts\SoftwareConstraints;

class PluginConfiguration extends Configuration implements Installable, SoftwareConstraints
{
    protected ?string $checksum;
    protected string $checksumType;
    protected string $name;
    protected string $softwareConstraints;
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

    protected function defaultConfig(): array
    {
        return [
            'distribution' => [
                'checksum' => null,
                'checksum-type' => ChecksumType::None->value,
            ],
            'minecraft-version' => '*',
        ];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSoftwareConstraint(): string
    {
        return $this->softwareConstraints;
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
        $this->softwareConstraints = $config['minecraft-version']; // TODO replace this with requirements -> minecraft/server
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
            'minecraft-version' => $this->softwareConstraints,
            'name' => $this->name,
            'version' => $this->version,
        ];
    }
}
