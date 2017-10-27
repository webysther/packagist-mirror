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

namespace Webs\Mirror;

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
    public function addConsole(InputInterface $input, OutputInterface $output):void;

    /**
     * Check if progress bar is enabled.
     *
     * @return boolean True if enabled
     */
    public function isEnabled():bool;

    /**
     * Start progress bar.
     *
     * @param  int    $total Total
     * @return void
     */
    public function start(int $total):void;

    /**
     * Update progress bar to some point.
     *
     * @param  int|integer $current Current value to set
     * @return void
     */
    public function update(int $current = 0):void;

    /**
     * Finish progress bar.
     *
     * @return void
     */
    public function end():void;
}
