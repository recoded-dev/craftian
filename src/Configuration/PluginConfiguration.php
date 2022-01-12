<?php

namespace Recoded\Craftian\Configuration;

use Recoded\Craftian\Contracts\Installable;
use Recoded\Craftian\Contracts\Requirements;
use Recoded\Craftian\Contracts\SoftwareConstraints;

class PluginConfiguration extends Configuration implements Installable, Requirements, SoftwareConstraints
{
    protected ?string $checksum;
    protected string $checksumType;
    protected string $name;
    protected array $requirements;
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
        ];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSoftwareConstraint(): string
    {
        return '*'; // TODO refactor
    }

    public function getType(): ConfigurationType
    {
        return ConfigurationType::Plugin;
    }

    public function getURL(): string
    {
        return $this->url;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @param array<string, mixed> $config
     */
    public function initialize(array $config): void
    {
        $this->checksum = $config['distribution']['checksum'] ?? null;
        $this->checksumType = $config['distribution']['checksum-type'] ?? ChecksumType::None->value;
        $this->name = $config['name'];
        $this->requirements = $config['require'] ?? [];
        $this->url = $config['distribution']['url'];
        $this->version = $config['version'];
    }

    public function requirements(): array
    {
        return $this->requirements;
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
            'requirements' => $this->requirements,
        ];
    }
}
