<?php

declare(strict_types=1);

/*
 * This file is part of the Packagist Mirror.
 *
 * For the full license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Webs\Mirror\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Webs\Mirror\Util;
use Dariuszp\CliProgressBar;

/**
 * Base command.
 *
 * @author Webysther Nunes <webysther@gmail.com>
 */
abstract class Base extends Command
{
    /**
     * Console params configuration.
     */
    protected function configure():void
    {
        $this->addOption(
            'info',
            null,
            InputOption::VALUE_NONE,
            'Show information about disk usage, execution time and memory usage'
        )
             ->addOption(
                 'no-progress',
                 null,
                 InputOption::VALUE_NONE,
                 'Don\'t show progress bar'
             );
    }

    /**
     * Execution.
     *
     * @param InputInterface  $input  Input console
     * @param OutputInterface $output Output console
     *
     * @return int 0 if pass, any another is error
     */
    public function execute(InputInterface $input, OutputInterface $output):int
    {
        $this->input = $input;
        $this->output = $output;

        $info = false;
        if ($input->getOption('info')) {
            $info = true;
            $util = new Util();
        }

        $this->determineMode();

        if ($this->childExecute($input, $output)) {
            return 1;
        }

        if ($info) {
            $util->showResults($input, $output);
        }

        return 0;
    }

    /**
     * Execution.
     *
     * @param InputInterface  $input  Input console
     * @param OutputInterface $output Output console
     *
     * @return int 0 if pass, any another is error
     */
    abstract protected function childExecute(InputInterface $input, OutputInterface $output):int;

    /**
     * Find hash and replace by *.
     *
     * @param string $name Name of provider or package
     *
     * @return string Shortname
     */
    protected function shortname(string $name):string
    {
        return preg_replace('/\$(\w*)/', '*', $name);
    }

    /**
     * Determine mode operation.
     */
    protected function determineMode():void
    {
        $this->hasQuiet = $this->output->isQuiet()
                            || $this->input->getOption('no-progress')
                            || $this->input->getOption('no-ansi');
    }

    /**
     * Start progress bar.
     *
     * @param int $total Total
     */
    protected function progressBarStart(int $total):void
    {
        if ($this->hasQuiet) {
            return;
        }

        $this->bar = new CliProgressBar($total, 0);
    }

    /**
     * Update progress bar.
     *
     * @param int $current Current value
     */
    protected function progressBarUpdate(int $current = 0):void
    {
        if ($this->hasQuiet) {
            return;
        }

        if ($current) {
            $this->bar->progress($current);

            return;
        }

        $this->bar->progress();
    }

    /**
     * Finish progress bar.
     */
    protected function progressBarFinish():void
    {
        if ($this->hasQuiet) {
            return;
        }

        $this->bar->end();
    }

    /**
     * Check if is gzip, if not compress.
     *
     * @param string $gzip
     *
     * @return string
     */
    protected function parseGzip(string $gzip):string
    {
        if (mb_strpos($gzip, "\x1f"."\x8b"."\x08") !== 0) {
            return gzencode($gzip);
        }

        return $gzip;
    }

    /**
     * Check if is gzip, if yes uncompress.
     *
     * @param string $gzip
     *
     * @return string
     */
    protected function unparseGzip(string $gzip):string
    {
        if (mb_strpos($gzip, "\x1f"."\x8b"."\x08") !== 0) {
            return $gzip;
        }

        return gzdecode($gzip);
    }
}
