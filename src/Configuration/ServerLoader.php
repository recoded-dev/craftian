<?php

namespace Recoded\Craftian\Configuration;

use Recoded\Craftian\Configuration\Locking\Lock;
use Recoded\Craftian\Craftian;

class ServerLoader
{
    protected string $cwd;

    public function __construct()
    {
        $this->cwd = Craftian::getCwd();
    }

    public function load(string $path = null): ServerBlueprint
    {
        $path ??= $this->cwd;

        $resource = @fopen($path . '/craftian.json', 'r');

        if ($resource === false) {
            throw new \Exception('No "craftian.json" file found');
        }

        $contents = stream_get_contents($resource);

        if ($contents === false) {
            throw new \Exception('Could not read "craftian.json" file');
        }

        $blueprint = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);

        if (!is_array($blueprint)) {
            throw new \Exception('Configuration loaded isn\'t a server configuration');
        }

        $blueprint = Blueprint::fromArray($blueprint);
        fclose($resource);

        if (!$blueprint instanceof ServerBlueprint) {
            throw new \Exception('Configuration loaded isn\'t a server configuration');
        }

        if (file_exists($lockPath = $path . '/craftian.lock')) {
            $lock = @fopen($lockPath, 'r');

            if ($lock === false) {
                throw new \Exception('Could not read "craftian.lock" file');
            }

            $lockContents = stream_get_contents($lock);

            if ($lockContents === false) {
                throw new \Exception('Could not read "craftian.lock" file');
            }

            $lockArray = json_decode($lockContents, true, flags: JSON_THROW_ON_ERROR);

            if (
                !is_array($lockArray)
                || !isset($lockArray['requirements'])
                || !is_array($lockArray['requirements'])
                || !isset($lockArray['time'])
                || !isset($lockArray['checksum'])
            ) {
                throw new \Exception('Lock file isn\'t valid');
            }

            $blueprint->setLock(Lock::fromArray($lockArray));
        }

        return $blueprint;
    }
}
