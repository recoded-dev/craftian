<?php

namespace Recoded\Craftian\Contracts;

interface Repository
{
    /**
     * Autocomplete a query for this repository.
     *
     * @param string $query
     * @return array<string>
     */
    public function autocomplete(string $query): array;

    /**
     * Get all versions of ...
     *
     * @param string $name
     * @return array<string>
     */
    public function get(string $name): array;

    /**
     * Array of all configurations this repository provides grouped by type.
     *
     * @return non-empty-array<string>
     */
    public function provides(): array;
}
