<?php

namespace Recoded\Craftian\Configuration;

use Recoded\Craftian\Contracts\Replacable;
use Recoded\Craftian\Contracts\Requirements;
use Recoded\Craftian\Contracts\SoftwareConstraints;

class PluginBlueprint extends Blueprint implements Replacable, Requirements, SoftwareConstraints
{
    protected ?string $checksum = null;
    protected string $checksumType;
    protected string $name;
    /**
     * @var array<string, string>
     */
    protected array $replacements = [];
    /**
     * @var array<string, string>
     */
    protected array $requirements = [];
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

        if (isset($config['require']) && is_array($config['require'])) {
            $this->requirements = $config['require'];
        }

        if (isset($config['replaces']) && is_array($config['replaces'])) {
            $this->replacements = $config['replaces'];
        }
    }

    public function installationFilename(): string
    {
        $safeName = preg_replace([
            '/[\/\\\\]+/',
            '/[^A-Za-z0-9]+/',
        ], [
            '-',
            '',
        ], $this->getName());

        return sprintf(
            '%s-%s.jar',
            $safeName,
            str_replace('.', '_', $this->getVersion()),
        );
    }

    public function installationLocation(): string
    {
        return '/plugins';
    }

    public function replaces(): array
    {
        return array_map(
            fn (string $version) => str_replace('self.version', $this->version, $version),
            $this->replacements,
        );
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
            'replaces' => $this->replacements,
            'requirements' => $this->requirements,
            'version' => $this->version,
        ];
    }
}
