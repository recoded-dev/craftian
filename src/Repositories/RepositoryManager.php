<?php

namespace Recoded\Craftian\Repositories;

use Recoded\Craftian\Configuration\ServerConfiguration;
use Recoded\Craftian\Contracts\Repository;
use Recoded\Craftian\Craftian;

class RepositoryManager
{
    /**
     * @var array<\Recoded\Craftian\Contracts\Repository>
     */
    protected array $repositories = [];

    final private function __construct()
    {
        //
    }

    /**
     * @return array<string>
     */
    public function available(): array
    {
        return array_reduce($this->repositories, function (array $carry, Repository $repository) {
            return array_merge_recursive($carry, $repository->provides());
        }, []);
    }

    public static function default(): static
    {
        $manager = new static();

        $manager->repositories = Craftian::defaultRepositories();

        return $manager;
    }

    public static function fromServer(ServerConfiguration $configuration): static
    {
        $manager = new static();

        if ($configuration->hasDefaultRepositories()) {
            $manager->repositories = Craftian::defaultRepositories();
        }

        foreach ($configuration->repositories() as $repository) {
            if (!is_array($repository)) {
                throw new \InvalidArgumentException('Repositories should be expressed in array-form');
            }

            if (!isset($repository['type']) || !is_string($repository['type']) || CustomRepositoryType::tryFrom($repository['type']) === null) {
                throw new \InvalidArgumentException('Unknown repository type');
            }

            $manager->repositories[] = CustomRepositoryType::from($repository['type'])->initialize($repository);
        }

        return $manager;
    }

    /**
     * @return array<\Recoded\Craftian\Configuration\Configuration>
     */
    public function get(string $name): array
    {
        return array_reduce($this->repositories, function (array $carry, Repository $repository) use ($name) {
            return array_merge($carry, $repository->get($name));
        }, []);
    }
}
