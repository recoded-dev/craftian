<?php

namespace Recoded\Craftian\Configuration;

enum ConfigurationType: string
{
    case Plugin = 'plugin';
    case Server = 'server';
    case Software = 'software';

    public function initialize(array $config): Configuration
    {
        return new (match ($this) {
            self::Plugin => PluginConfiguration::class,
            self::Server => ServerConfiguration::class,
            self::Software => SoftwareConfiguration::class,
        })($config);
    }
}
