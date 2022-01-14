<?php

namespace Recoded\Craftian\Configuration\Locking;

use Composer\Semver\Semver;
use Composer\Semver\VersionParser;
use Recoded\Craftian\Configuration\Blueprint;
use Recoded\Craftian\Configuration\ChecksumType;
use Recoded\Craftian\Configuration\ServerBlueprint;
use Recoded\Craftian\Contracts\Installable;
use Recoded\Craftian\Contracts\Replacable;
use Recoded\Craftian\Contracts\Requirements;
use Recoded\Craftian\Repositories\RepositoryManager;

class Locker
{
    /**
     * @var array<string, array<\Recoded\Craftian\Configuration\Blueprint&\Recoded\Craftian\Contracts\Installable>>
     */
    protected array $cache;
    protected RepositoryManager $manager;
    protected VersionParser $versionParser;

    public function __construct(
        protected ServerBlueprint $server,
    ) {
        $this->manager = RepositoryManager::fromServer($this->server);
        $this->versionParser = new VersionParser();
    }

    /**
     * @param array<string, \Recoded\Craftian\Configuration\Blueprint> $lock
     * @param string $requirement
     * @return array<string>
     */
    protected function findConstraintsFor(array $lock, string $requirement): array
    {
        $requirements = array_filter(
            $lock,
            fn (Blueprint $blueprint) => $blueprint instanceof Requirements,
        );
        $constraints = array_map(function (Blueprint&Requirements $blueprint) use ($requirement) {
            return $blueprint->requirements()[$requirement] ?? null;
        }, $requirements);

        $constraints[] = $this->server->requirements()[$requirement] ?? null;

        return array_values(array_filter($constraints));
    }

    /**
     * @param string $requirement
     * @param array<string> $constraints
     * @param array<string, string> $installed
     * @return array{array<string, string>, \Recoded\Craftian\Configuration\Blueprint&\Recoded\Craftian\Contracts\Installable}|null
     */
    protected function findLock(string $requirement, array $constraints, array $installed): ?array
    {
        $possible = array_filter(
            $this->get($requirement),
            function (Blueprint&Installable $blueprint) use ($constraints, $installed) {
                if ($blueprint instanceof Requirements) {
                    foreach ($blueprint->requirements() as $requirement => $constraint) {
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
                    if (!Semver::satisfies($blueprint->getVersion(), $constraint)) {
                        return false;
                    }
                }

                return true;
            },
        );

        if (empty($possible)) {
            return null;
        }

        $blueprint = array_values($possible)[0];

        if (!$blueprint instanceof Requirements) {
            return [[], $blueprint];
        }

        /** @var \Recoded\Craftian\Configuration\Blueprint&\Recoded\Craftian\Contracts\Installable&\Recoded\Craftian\Contracts\Requirements $blueprint */

        return [
            array_filter(
                $blueprint->requirements(),
                fn (string $requirement) => !isset($installed[$requirement]),
                ARRAY_FILTER_USE_KEY,
            ),
            $blueprint,
        ];
    }

    /**
     * @param string $requirement
     * @return array<\Recoded\Craftian\Configuration\Blueprint&\Recoded\Craftian\Contracts\Installable>
     */
    protected function get(string $requirement): array
    {
        return $this->cache[$requirement] ??= $this->sort(
            array_filter(
                $this->manager->get($requirement),
                fn (Blueprint $configuration) => $configuration instanceof Installable,
            ),
        );
    }

    public function lock(): Lock
    {
        $lock = [];

        $toLock = $this->server->requirements();
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

        $summary = [];

        /** @var \Recoded\Craftian\Configuration\Blueprint&\Recoded\Craftian\Contracts\Installable $blueprint */
        foreach ($lock as $requirement => $blueprint) {
            $summary[$requirement] = $blueprint->getChecksum();
        }

        $jsonSummary = json_encode($summary);

        if ($jsonSummary === false) {
            throw new \JsonException('Cannot summarize lock file');
        }

        return new Lock(
            array_values($lock),
            time(),
            hash(ChecksumType::Sha256->value, $jsonSummary),
        );
    }

    /**
     * @param array<\Recoded\Craftian\Configuration\Blueprint&\Recoded\Craftian\Contracts\Installable> $blueprints
     * @return array<\Recoded\Craftian\Configuration\Blueprint&\Recoded\Craftian\Contracts\Installable>
     */
    protected function sort(array $blueprints): array
    {
        $versions = array_map(fn (Blueprint&Installable $blueprint) => $blueprint->getVersion(), $blueprints);

        $sorted = Semver::rsort($versions);

        $last = count($blueprints);

        usort($blueprints, function (Blueprint&Installable $blueprint) use ($last, $sorted) {
            $index = array_search($blueprint->getVersion(), $sorted);

            return is_int($index) ? $index : $last;
        });

        return $blueprints;
    }
}
