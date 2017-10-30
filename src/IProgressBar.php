<?php

declare(strict_types=1);

/*
 * This file is part of the Packagist Mirror.
 *
 * For the full license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Webs\Mirror;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Progress bar for console.
 *
 * @author Webysther Nunes <webysther@gmail.com>
 */
interface IProgressBar
{
    /**
     * Class constructor.
     *
     * @param InputInterface  $input  Input
     * @param OutputInterface $output Output
     */
    public function setConsole(InputInterface $input, OutputInterface $output):void;

    /**
     * Check if progress bar is enabled.
     *
     * @return bool True if enabled
     */
    public function isDisabled():bool;

    /**
     * Start progress bar.
     *
     * @param int $total Total
     */
    public function start(int $total):IProgressBar;

    /**
     * Update progress bar to some point.
     *
     * @param int|int $current Current value to set
     */
    public function progress(int $current = 0):IProgressBar;

    /**
     * Finish progress bar.
     */
    public function end():IProgressBar;
}
