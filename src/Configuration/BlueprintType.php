<?php

namespace Recoded\Craftian\Configuration;

enum BlueprintType: string
{
    case Plugin = 'plugin';
    case Server = 'server';
    case Software = 'software';

    /**
     * @param array<string, mixed> $config
     * @return \Recoded\Craftian\Configuration\Blueprint
     */
    public function initialize(array $config): Blueprint
    {
        return new (match ($this) {
            self::Plugin => PluginBlueprint::class,
            self::Server => ServerBlueprint::class,
            self::Software => SoftwareBlueprint::class,
        })($config);
    }

    public static function fromBlueprint(Blueprint $blueprint): self
    {
        return match (get_class($blueprint)) {
            PluginBlueprint::class => self::Plugin,
            ServerBlueprint::class => self::Server,
            SoftwareBlueprint::class => self::Software,
            default => throw new \Exception('Unknown blueprint class'),
        };
    }
}
