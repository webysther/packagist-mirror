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
use Symfony\Component\Console\Input\{InputInterface, InputOption};
use Symfony\Component\Console\Output\OutputInterface;
use Webs\Mirror\ShortName;
use Webs\Mirror\IProgressBar;
use Webs\Mirror\Filesystem;
use Webs\Mirror\Http;

/**
 * Base command.
 *
 * @author Webysther Nunes <webysther@gmail.com>
 */
class Base extends Command
{
    use ShortName;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var IProgressBar
     */
    protected $progressBar;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Http
     */
    protected $http;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->addOption(
            'no-progress',
            null,
            InputOption::VALUE_NONE,
            "Don't show progress bar"
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
    }

    /**
     * Add a progress bar
     *
     * @param  IProgressBar $progressBar
     * @return void
     */
    public function addProgressBar(IProgressBar $progressBar):void
    {
        $this->progressBar = $progressBar;
    }

    /**
     * Add a fileSystem
     *
     * @param  Filesystem $fileSystem
     * @return void
     */
    public function addFilesystem(Filesystem $filesystem):void
    {
        $this->filesystem = $fileSystem;
    }

    /**
     * Add a http
     *
     * @param  Http $http
     * @return void
     */
    public function addHttp(Http $http):void
    {
        $this->http = $http;
    }
}
