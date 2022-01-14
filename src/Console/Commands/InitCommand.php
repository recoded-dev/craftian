<?php

namespace Recoded\Craftian\Console\Commands;

use Recoded\Craftian\Configuration\BlueprintType;
use Recoded\Craftian\Craftian;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends Command
{
    protected static $defaultDescription = 'Boilerplate a server';
    protected static $defaultName = 'init';

    protected function assertIsNotInitialized(): void
    {
        if (file_exists(Craftian::getCwd() . '/craftian.json')) {
            throw new \Exception('Craftian already initialized');
        }
    }

    /**
     * @param \JsonSerializable $data
     * @throws \Exception
     */
    protected function initializeJson(\JsonSerializable $data): void
    {
        $file = fopen(Craftian::getCwd() . '/craftian.json', 'a');
        $data = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if ($file === false || $data === false) {
            throw new \Exception('Couldn\'t initialize Craftian');
        }

        fwrite($file, $data);
        fclose($file);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->assertIsNotInitialized();

        $this->initializeJson(BlueprintType::Server->initialize([]));

        $this->updateOrCreateGitIgnore();

        return static::SUCCESS;
    }

    protected function updateOrCreateGitIgnore(): void
    {
        $path = Craftian::getCwd() . '/.gitignore';

        if (file_exists($path)) {
            $contents = file_get_contents($path);

            if ($contents === false) {
                throw new \Exception('Cannot update .gitignore file');
            }

            if (str_contains($contents, 'server.jar')) {
                return;
            }

            file_put_contents($path, "plugins/*.jar\nserver.jar\n", FILE_APPEND);
        } else {
            $resource = fopen($path, 'a+');

            if ($resource === false) {
                throw new \Exception('Cannot create .gitignore file');
            }

            fwrite($resource, "plugins/*.jar\nserver.jar\n");
            fclose($resource);
        }
    }
}
