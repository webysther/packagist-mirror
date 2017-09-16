<?php

declare(strict_types=1);

/*
 * This file is part of the Packagist Mirror.
 *
 * For the full license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace League\Mirror;

use SebastianBergmann\Version;
use Symfony\Component\Console\Application;
use League\Mirror\Command\Create;
use League\Mirror\Command\Snapshot;

/**
 * Entrypoint for application.
 *
 * @author Webysther Nunes <webysther@gmail.com>
 */
class Main extends Application
{
    /**
     * Start application.
     */
    public function __construct()
    {
        $version = new Version('1.0.0', dirname(__DIR__.'/..'));
        parent::__construct('Packagist Mirror', $version->getVersion());

        // Add all commands
        $this->add(new Create());
        $this->add(new Snapshot());
    }
}
