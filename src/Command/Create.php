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
 * Create a mirror.
 *
 * @author Webysther Nunes <webysther@gmail.com>
 */
class Create extends Command
{
    /**
     * Console description.
     *
     * @var string
     */
    protected $description = 'Create packagist mirror';

    /**
     * Console params configuration.
     */
    protected function configure():void
    {
        $this->setName('create')->setDescription($this->description);
    }

    /**
     * Execution.
     *
     * @param InputInterface  $input  Input console
     * @param OutputInterface $output Output console
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        exit(1);
    }
}
