<?php

namespace Recoded\Craftian\Contracts;

use Recoded\Craftian\Configuration\Locking\Lock;

interface Locking
{
    /**
     * Get the lock for the blueprint.
     *
     * @return \Recoded\Craftian\Configuration\Locking\Lock
     */
    public function lock(): Lock;

    /**
     * Set the lock for the blueprint.
     *
     * @param \Recoded\Craftian\Configuration\Locking\Lock $lock
     * @return void
     */
    public function setLock(Lock $lock): void;
}
