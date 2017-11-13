<?php

declare(strict_types=1);

/*
 * This file is part of the Packagist Mirror.
 *
 * For the full license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Webs\Mirror;

use Dariuszp\CliProgressBar;

/**
 * Progress bar for console.
 *
 * @author Webysther Nunes <webysther@gmail.com>
 */
class ProgressBar implements IProgressBar
{
    use Console;

    /**
     * @var bool
     */
    protected $disabled;

    /**
     * @var CliProgressBar
     */
    protected $progressBar;

    /**s
     * @var int
     */
    protected $total;

    /**
     * {@inheritdoc}
     */
    public function isDisabled():bool
    {
        if (isset($this->disabled)) {
            return $this->disabled;
        }

        $isQuiet = $this->output->isQuiet();
        $noProgress = $this->input->getOption('no-progress');
        $noAnsi = $this->input->getOption('no-ansi');

        $this->disabled = $isQuiet || $noProgress || $noAnsi;

        return $this->disabled;
    }

    /**
     * {@inheritdoc}
     */
    public function start(int $total):IProgressBar
    {
        if ($this->isDisabled()) {
            return $this;
        }

        $this->total = $total;
        $this->progressBar = new CliProgressBar($total, 0);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function progress(int $current = 0):IProgressBar
    {
        if ($this->isDisabled()) {
            return $this;
        }

        if ($current) {
            $this->progressBar->progress($current);

            return $this;
        }

        $this->progressBar->progress();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function end():IProgressBar
    {
        if ($this->isDisabled()) {
            return $this;
        }

        $this->progressBar->progress($this->total);
        $this->progressBar->end();

        return $this;
    }
}
