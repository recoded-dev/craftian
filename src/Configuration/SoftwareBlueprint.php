<?php

namespace Recoded\Craftian\Configuration;

use Recoded\Craftian\Contracts\Replacable;

class SoftwareBlueprint extends Blueprint implements Replacable
{
    protected ?string $checksum = null;
    protected string $checksumType;
    protected string $name;
    /**
     * @var array<string, string>
     */
    protected array $replacements = [];
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
        if (
            isset($config['distribution'])
            && is_array($config['distribution'])
            && isset($config['distribution']['checksum'])
            && is_string($config['distribution']['checksum'])
        ) {
            $this->checksum = $config['distribution']['checksum'];
        }

        if (
            isset($config['distribution'])
            && is_array($config['distribution'])
            && isset($config['distribution']['checksum-type'])
            && is_string($config['distribution']['checksum-type'])
        ) {
            $this->checksumType = $config['distribution']['checksum-type'];
        } else {
            $this->checksumType = ChecksumType::None->value;
        }

        if (
            !isset($config['name'])
            || !is_string($config['name'])
        ) {
            throw new \InvalidArgumentException('Configuration should have a name');
        }

        if (
            !isset($config['version'])
            || !is_string($config['version'])
        ) {
            throw new \InvalidArgumentException('Configuration should have a version');
        }

        if (
            !isset($config['distribution'])
            || !is_array($config['distribution'])
            || !isset($config['distribution']['url'])
            || !is_string($config['distribution']['url'])
        ) {
            throw new \InvalidArgumentException('Configuration should have a distribution URL');
        }

        $this->name = $config['name'];
        $this->url = $config['distribution']['url'];
        $this->version = $config['version'];

        if (isset($config['replaces']) && is_array($config['replaces'])) {
            $this->replacements = $config['replaces'];
        }
    }

    public function installationFilename(): string
    {
        return 'server.jar';
    }

    public function installationLocation(): string
    {
        return '/';
    }

    public function replaces(): array
    {
        return array_map(
            fn (string $version) => str_replace('self.version', $this->version, $version),
            $this->replacements,
        );
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
            'replaces' => $this->replacements,
            'version' => $this->version,
        ];
    }
}
