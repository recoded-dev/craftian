<?php

namespace Recoded\Craftian\Repositories;

use Recoded\Craftian\Configuration\ServerConfiguration;
use Recoded\Craftian\Contracts\Repository;
use Recoded\Craftian\Craftian;

class RepositoryManager
{
    protected array $repositories = [];

    final private function __construct() { }

    public function available(): array
    {
        return array_unique(array_reduce($this->repositories, function (array $carry, Repository $repository) {
            return array_merge_recursive($carry, $repository->provides());
        }, []));
    }

    public static function fromServer(ServerConfiguration $configuration): static
    {
        $manager = new static();

        if ($configuration->hasDefaultRepositories()) {
            $manager->repositories = Craftian::defaultRepositories();
        }

        foreach ($configuration->repositories() as $repository) {
            // TODO initialize and add.
        }

        return $manager;
    }
}
