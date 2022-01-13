<?php

namespace Recoded\Craftian\Configuration\Locking;

use Composer\Semver\Semver;
use Composer\Semver\VersionParser;
use Recoded\Craftian\Configuration\Configuration;
use Recoded\Craftian\Configuration\LockedConfiguration;
use Recoded\Craftian\Configuration\ServerConfiguration;
use Recoded\Craftian\Contracts\Installable;
use Recoded\Craftian\Contracts\Replacable;
use Recoded\Craftian\Contracts\Requirements;
use Recoded\Craftian\Repositories\RepositoryManager;

class Locker
{
    /**
     * @var array<string, array<\Recoded\Craftian\Configuration\Configuration&\Recoded\Craftian\Contracts\Installable>>
     */
    protected array $cache;
    protected RepositoryManager $manager;
    protected VersionParser $versionParser;

    public function __construct(
        protected ServerConfiguration $configuration,
    ) {
        $this->manager = RepositoryManager::fromServer($this->configuration);
        $this->versionParser = new VersionParser();
    }

    /**
     * @param array<string, \Recoded\Craftian\Configuration\Configuration> $lock
     * @param string $requirement
     * @return array<string>
     */
    protected function findConstraintsFor(array $lock, string $requirement): array
    {
        $requirements = array_filter($lock, fn (Configuration $configuration) => $configuration instanceof Requirements);
        $constraints = array_map(function (Requirements $configuration) use ($requirement) {
            return $configuration->requirements()[$requirement] ?? null;
        }, $requirements);

        $constraints[] = $this->configuration->requirements()[$requirement] ?? null;

        return array_values(array_filter($constraints));
    }

    /**
     * @param string $requirement
     * @param array<string> $constraints
     * @param array<string, string> $installed
     * @return array{array<string, string>, \Recoded\Craftian\Configuration\Configuration&\Recoded\Craftian\Contracts\Installable}|null
     */
    protected function findLock(string $requirement, array $constraints, array $installed): ?array
    {
        $possible = array_filter($this->get($requirement), function (Configuration&Installable $configuration) use ($constraints, $installed) {
            if ($configuration instanceof Requirements) {
                foreach ($configuration->requirements() as $requirement => $constraint) {
                    $availableVersions = array_map(
                        fn (Installable $installable) => $installable->getVersion(),
                        $this->get($requirement),
                    );

                    $satisfiedVersions = Semver::satisfiedBy($availableVersions, $constraint);

                    if (empty($satisfiedVersions)) {
                        return false;
                    }

                    if (!isset($installed[$requirement])) {
                        continue;
                    }

                    if (!Semver::satisfies($installed[$requirement], $constraint)) {
                        return false;
                    }
                }
            }

            foreach ($constraints as $constraint) {
                if (!Semver::satisfies($configuration->getVersion(), $constraint)) {
                    return false;
                }
            }

            return true;
        });

        if (empty($possible)) {
            return null;
        }

        $configuration = array_values($possible)[0];

        if (!$configuration instanceof Requirements) {
            return [[], $configuration];
        }

        /** @var \Recoded\Craftian\Configuration\Configuration&\Recoded\Craftian\Contracts\Installable&\Recoded\Craftian\Contracts\Requirements $configuration */

        return [
            array_filter(
                $configuration->requirements(),
                fn (string $requirement) => !isset($installed[$requirement]),
                ARRAY_FILTER_USE_KEY,
            ),
            $configuration,
        ];
    }

    /**
     * @param string $requirement
     * @return array<\Recoded\Craftian\Configuration\Configuration&\Recoded\Craftian\Contracts\Installable>
     */
    protected function get(string $requirement): array
    {
        return $this->cache[$requirement] ??= $this->sort(
            array_filter(
                $this->manager->get($requirement),
                fn (Configuration $configuration) => $configuration instanceof Installable,
            ),
        );
    }

    public function lock(): LockedConfiguration
    {
        $lock = [];

        $toLock = $this->configuration->requirements();
        $installed = [];

        do {
            foreach ($toLock as $requirement => $constraint) {
                $found = $this->findLock($requirement, $this->findConstraintsFor($lock, $requirement), $installed);

                if ($found === null) {
                    throw new \Exception("Cannot find a common version for {$requirement}");
                }

                [
                    $toInstall,
                    $lock[$requirement],
                ] = $found;

                $installed[$requirement] = $found[1]->getVersion();

                // TODO check if this is fraudulent 😅
                if ($found[1] instanceof Replacable) {
                    foreach ($found[1]->replaces() as $replaceRequirement => $replaceVersion) {
                        $installed[$replaceRequirement] ??= $replaceVersion;
                    }
                }

                unset($toLock[$requirement]);

                $toLock = array_merge($toLock, $toInstall);
            }
        } while (!empty($toLock));

        return new LockedConfiguration(array_values($lock));
    }

    /**
     * @param array<\Recoded\Craftian\Configuration\Configuration&\Recoded\Craftian\Contracts\Installable> $configurations
     * @return array<\Recoded\Craftian\Configuration\Configuration&\Recoded\Craftian\Contracts\Installable>
     */
    protected function sort(array $configurations): array
    {
        $versions = array_map(fn (Installable $installable) => $installable->getVersion(), $configurations);

        $sorted = Semver::rsort($versions);

        $last = count($configurations);

        usort($configurations, function (Configuration&Installable $installable) use ($last, $sorted) {
            $index = array_search($installable->getVersion(), $sorted);

            return is_int($index) ? $index : $last;
        });

        return $configurations;
    }
}
