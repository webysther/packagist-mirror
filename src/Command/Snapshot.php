<?php

declare(strict_types=1);

/*
 * This file is part of the Packagist Mirror.
 *
 * For the full license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace League\Mirror\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Make a snapshot of all data to available to other mirror.
 *
 * @author Webysther Nunes <webysther@gmail.com>
 */
class Snapshot extends Command
{
    /**
     * Console description.
     *
     * @var string
     */
    protected $description = <<<'TEXT'
Make snapshot of mirror.

    <comment>Don't use this option if you plan for private mirror.</comment>

TEXT;

    /**
     * Console params configuration.
     */
    protected function configure():void
    {
        $this->setName('snapshot')->setDescription($this->description);
    }

    /**
     * Execution.
     *
     * @param InputInterface  $input  Input console
     * @param OutputInterface $output Output console
     *
     * @return int 0 if pass, any another is error
     */
    protected function execute(InputInterface $input, OutputInterface $output):int
    {
        $input;
        $output;

        return 0;
    }
}
