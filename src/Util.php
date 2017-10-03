<?php

declare(strict_types=1);

/*
 * This file is part of the Packagist Mirror.
 *
 * For the full license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Webs\Mirror;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use utilphp\util as File;
use Carbon\Carbon;

/**
 * Helper class to show data after command execution.
 *
 * @author Webysther Nunes <webysther@gmail.com>
 */
class Util
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        // Start clock
        $this->start = Carbon::now();

        if ($this->isInsideDocker()) {
            // Show Kilobytes
            $this->currentDisk = $this->getBytesFromPublic();
        }
    }

    /**
     * Execution.
     *
     * @param InputInterface  $input  Input console
     * @param OutputInterface $output Output console
     */
    public function showResults(InputInterface $input, OutputInterface $output):void
    {
        $style = new SymfonyStyle($input, $output);
        $style->section('Results');

        $memory = File::size_format(memory_get_peak_usage(true));
        $time = Carbon::now()->diffForHumans($this->start);

        if ($this->isInsideDocker()) {
            // Current kilobytes diference
            $nextDisk = $this->getBytesFromPublic();
            $saved = $this->currentDisk - $nextDisk;

            $currentDisk = File::size_format($this->currentDisk, 1);
            $nextDisk = File::size_format($nextDisk, 1);

            if ($currentDisk != $nextDisk) {
                $output->writeln(
                    '<comment>Before:'.$currentDisk.'</>'.PHP_EOL.
                    '<info>Current:'.$nextDisk.'</>'.PHP_EOL
                );
            }

            // More than 4 Kib?
            if ($saved > 4096) {
                $saved = File::size_format($saved);
                $output->writeln('A total of '.$saved.' was saved.'.PHP_EOL);
            }

            $output->writeln("Total disk free:\t".$nextDisk);
        }

        $output->writeln("Total memory usage:\t".$memory);
        $output->writeln("Total execution:\t".$time);
    }

    /**
     * Get bytes usage of partition public directory.
     *
     * @return float Bytes used
     */
    public function getBytesFromPublic():float
    {
        return disk_free_space(getenv('PUBLIC_DIR'));
    }

    /**
     * Check is running inside docker.
     *
     * @return bool True if is inside docker
     */
    public function isInsideDocker():bool
    {
        return file_exists('/.dockerenv');
    }
}
