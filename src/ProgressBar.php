<?php

declare(strict_types=1);

/*
 * This file is part of the Packagist Mirror.
 *
 * For the full license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Dariuszp\CliProgressBar;

namespace Webs\Mirror;

/**
 * Progress bar for console.
 *
 * @author Webysther Nunes <webysther@gmail.com>
 */
class ProgressBar implements IProgressBar
{
    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var boolean
     */
    protected $disabled = true;

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
    public function addConsole(InputInterface $input, OutputInterface $output):void
    {
        $this->input = $input;
        $this->output = $output;
        $this->disabled = !$this->isEnabled();
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled():bool
    {
        $isQuiet = $this->output->isQuiet();
        $noProgress = $this->input->getOption('no-progress');
        $noAnsi = $this->input->getOption('no-ansi');

        if ($isQuiet || $noProgress || $noAnsi) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function start(int $total):ProgressBar
    {
        if ($this->disabled) {
            return $this;
        }

        $this->total = $total;
        $this->progressBar = new CliProgressBar($total, 0);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function progress(int $current = 0):ProgressBar
    {
        if ($this->disabled) {
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
    public function end():ProgressBar
    {
        if ($this->disabled) {
            return $this;
        }

        $this->progressBar->progress($this->total);
        $this->progressBar->end();
        return $this;
    }
}
