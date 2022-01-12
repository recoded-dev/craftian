<?php

namespace Recoded\Craftian\Configuration\Locking;

use Composer\Semver\Semver;
use Composer\Semver\VersionParser;
use Recoded\Craftian\Configuration\LockedConfiguration;
use Recoded\Craftian\Configuration\ServerConfiguration;
use Recoded\Craftian\Contracts\Installable;
use Recoded\Craftian\Repositories\RepositoryManager;

class Locker
{
    protected RepositoryManager $manager;
    protected VersionParser $versionParser;

    public function __construct(
        protected ServerConfiguration $configuration,
    ) {
        $this->manager = RepositoryManager::fromServer($this->configuration);
        $this->versionParser = new VersionParser();
    }

    public function lock(): LockedConfiguration
    {
        $baseRequirements = $this->configuration->requirements();

        $baseRequirements = array_map(function (string $requirement, string $constraint) {
            $versions = $this->manager->get($requirement);

            $raw = Semver::rsort(
                array_map(fn (Installable $installable) => $installable->getVersion(), $versions),
            );

            $satisfied = Semver::satisfiedBy($raw, $constraint);
            $first = $satisfied[0] ?? null;

            if ($first === null) {
                throw new \InvalidArgumentException(
                    "Unable to find version matching constraint {$constraint} for {$requirement}",
                );
            }

            /** @var \Recoded\Craftian\Contracts\Installable $version */
            foreach ($versions as $version) {
                if ($version->getVersion() === $first) {
                    return $version;
                }
            }

            return null;
        }, array_keys($baseRequirements), $baseRequirements);

        return new LockedConfiguration(array_filter($baseRequirements));
    }
}
