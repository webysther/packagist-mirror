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
use Webs\Mirror\ShortName;
use Webs\Mirror\IProgressBar;
use Webs\Mirror\Filesystem;
use Webs\Mirror\Http;
use Webs\Mirror\Provider;
use Webs\Mirror\Package;

/**
 * Base command.
 *
 * @author Webysther Nunes <webysther@gmail.com>
 */
class Base extends Command
{
    use ShortName;

    /**
     * @var bool
     */
    protected $initialized = false;

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
     * @var Provider
     */
    protected $provider;

    /**
     * @var Package
     */
    protected $package;

    /**
     * @var int
     */
    protected $exitCode;

    /**
     * @var bool
     */
    protected $verboseVerbose = false;

    /**
     * @var int
     */
    const VV = OutputInterface::VERBOSITY_VERBOSE;

    /**
     * Main files.
     */
    const MAIN = 'packages.json';
    const DOT = '.packages.json';
    const INIT = '.init';
    const TO = 'p';

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
        $this->verboseVerbose = $this->output->getVerbosity() >= self::VV;
    }

    /**
     * @return bool
     */
    public function isVerbose():bool
    {
        return $this->verboseVerbose;
    }

    /**
     * Add a progress bar.
     *
     * @param IProgressBar $progressBar
     *
     * @return Base
     */
    public function setProgressBar(IProgressBar $progressBar):Base
    {
        $this->progressBar = $progressBar;

        return $this;
    }

    /**
     * Add a fileSystem.
     *
     * @param Filesystem $fileSystem
     *
     * @return Base
     */
    public function setFilesystem(Filesystem $filesystem):Base
    {
        $this->filesystem = $filesystem;

        return $this;
    }

    /**
     * Add a http.
     *
     * @param Http $http
     *
     * @return Base
     */
    public function setHttp(Http $http):Base
    {
        $this->http = $http;

        return $this;
    }

    /**
     * Add a provider.
     *
     * @param Provider $provider
     *
     * @return Base
     */
    public function setProvider(Provider $provider):Base
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * Add a packages.
     *
     * @param Package $package
     *
     * @return Base
     */
    public function setPackage(Package $package):Base
    {
        $this->package = $package;

        return $this;
    }

    /**
     * @param int $exit
     *
     * @return Base
     */
    protected function setExitCode(int $exit):Base
    {
        $this->exitCode = $exit;

        return $this;
    }

    /**
     * @return int
     */
    protected function getExitCode():int
    {
        return $this->stop() ? $this->exitCode : 0;
    }

    /**
     * @return bool
     */
    protected function stop():bool
    {
        return isset($this->exitCode);
    }
}
