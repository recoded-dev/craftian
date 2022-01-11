<?php

namespace Recoded\Craftian;

use Recoded\Craftian\Console\ProgressBarFormat;
use Recoded\Craftian\Contracts\Installable;
use Symfony\Component\Console\Helper\ProgressBar;

class InstallableProgressBarUpdater
{
    protected bool $finished = false;

    public function __construct(
        protected Installable $installable,
        protected ProgressBar $progressBar,
    ) {
        $progressBar->setFormat(ProgressBarFormat::DOWNLOADING_VERSION->value);
        $progressBar->setMessage($this->installable->getName(), 'downloadable');
        $progressBar->setMessage($this->installable->getVersion(), 'version');
    }

    public function __invoke(float $downloadSize, float $downloaded): void
    {
        if ($this->finished || $downloadSize <= 0) {
            return;
        }

        $this->progressBar->setMaxSteps(round($downloadSize));
        $this->progressBar->setProgress(round($downloaded));

        if ($downloadSize === $downloaded) {
            $this->finished = true;
            $this->progressBar->finish();
        }
    }
}
