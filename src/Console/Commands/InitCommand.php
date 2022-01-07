<?php

namespace Recoded\Craftian\Console\Commands;

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
     * @param array<array-key, mixed> $data
     * @throws \Exception
     */
    protected function initializeJson(array $data): void
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

        $this->initializeJson([
            'require' => [],
        ]);

        return static::SUCCESS;
    }
}
