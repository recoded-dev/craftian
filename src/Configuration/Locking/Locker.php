<?php

namespace Recoded\Craftian\Configuration\Locking;

use Composer\Semver\Semver;
use Composer\Semver\VersionParser;
use Recoded\Craftian\Configuration\Configuration;
use Recoded\Craftian\Configuration\LockedConfiguration;
use Recoded\Craftian\Configuration\ServerConfiguration;
use Recoded\Craftian\Contracts\Installable;
use Recoded\Craftian\Contracts\Requirements;
use Recoded\Craftian\Repositories\RepositoryManager;

class Locker
{
    protected array $cache;
    protected RepositoryManager $manager;
    protected VersionParser $versionParser;

    public function __construct(
        protected ServerConfiguration $configuration,
    ) {
        $this->manager = RepositoryManager::fromServer($this->configuration);
        $this->versionParser = new VersionParser();
    }

    protected function findConstraintsFor(array $lock, string $requirement): array
    {
        $requirements = array_filter($lock, fn (Configuration $configuration) => $configuration instanceof Requirements);
        $constraints = array_map(function (Requirements $configuration) use ($requirement) {
            return $configuration->requirements()[$requirement] ?? null;
        }, $requirements);

        $constraints[] = $this->configuration->requirements()[$requirement] ?? null;

        return array_values(array_filter($constraints));
    }

    protected function findLock(string $requirement, array $constraints, array $installed): ?array
    {
        $possible = array_filter($this->get($requirement), function (Configuration $configuration) use ($constraints, $installed) {
            if (!$configuration instanceof Installable) {
                return false;
            }

            if ($configuration instanceof Requirements) {
                foreach ($configuration->requirements() as $requirement => $constraint) {
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

        return [
            array_filter(
                $configuration->requirements(),
                fn (string $requirement) => !isset($installed[$requirement]),
                ARRAY_FILTER_USE_KEY,
            ),
            $configuration,
        ];
    }

    protected function get(string $requirement): array
    {
        return $this->cache[$requirement] ??= $this->sort(
            $this->manager->get($requirement),
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
                unset($toLock[$requirement]);

                $toLock = array_merge($toLock, $toInstall);
            }
        } while (!empty($toLock));

        dd(array_values($lock));

        return new LockedConfiguration(array_filter($baseRequirements));
    }

    protected function sort(array $configurations): array
    {
        $installables = array_filter($configurations, fn (Configuration $configuration) => $configuration instanceof Installable);
        $versions = array_map(fn (Installable $installable) => $installable->getVersion(), $installables);

        $sorted = Semver::rsort($versions);

        usort($installables, function (Installable $installable) use ($sorted) {
            return array_search($installable->getVersion(), $sorted);
        });

        return $installables;
    }
}
