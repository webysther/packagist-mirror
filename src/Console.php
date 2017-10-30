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
 * Trait to gzip operations.
 *
 * @author Webysther Nunes <webysther@gmail.com>
 */
trait Console
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
     * {@inheritdoc}
     */
    public function setConsole(InputInterface $input, OutputInterface $output):void
    {
        $this->input = $input;
        $this->output = $output;
    }
}
