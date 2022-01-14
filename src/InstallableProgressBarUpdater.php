<?php

namespace Recoded\Craftian;

use Recoded\Craftian\Contracts\Installable;
use Symfony\Component\Console\Helper\ProgressBar;

class InstallableProgressBarUpdater
{
    protected bool $finished = false;

    public function __construct(
        protected Installable $installable,
        protected ProgressBar $progressBar,
    ) {
        $progressBar->setMessage(
            sprintf('%s (%s)', $this->installable->getName(), $this->installable->getVersion()),
            'downloadable',
        );
    }

    public function __invoke(float $downloadSize, float $downloaded): void
    {
        if ($this->finished || $downloadSize <= 0) {
            return;
        }

        $this->progressBar->setMaxSteps((int) round($downloadSize / 1024 / 1024));
        $this->progressBar->setProgress((int) round($downloaded / 1024 / 1024));

        if ($downloadSize === $downloaded) {
            $this->finished = true;
            $this->progressBar->finish();
        }
    }
}
