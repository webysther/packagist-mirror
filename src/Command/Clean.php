<?php

declare(strict_types=1);

/*
 * This file is part of the Packagist Mirror.
 *
 * For the full license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Webs\Mirror\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use stdClass;

/**
 * Clean mirror outdated files.
 *
 * @author Webysther Nunes <webysther@gmail.com>
 */
class Clean extends Base
{
    /**
     * @var array
     */
    protected $changed = [];

    /**
     * @var array
     */
    protected $removed = [];

    /**
     * @var bool
     */
    protected $isScrub = false;

    /**
     * {@inheritdoc}
     */
    public function __construct($name = '')
    {
        parent::__construct('clean');
        $this->setDescription(
            'Clean outdated files of mirror'
        );
    }

    /**
     * Console params configuration.
     */
    protected function configure():void
    {
        parent::configure();
        $this->addOption(
            'scrub',
            null,
            InputOption::VALUE_NONE,
            'Check all directories for old files, use only to check all disk'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output):int
    {
        $this->initialize($input, $output);
        $this->bootstrap();

        if ($input->hasOption('scrub') && $input->getOption('scrub')) {
            $this->isScrub = true;
        }

        $this->flushProviders();
        $this->flushPackages();

        if (!count($this->changed)) {
            $output->writeln('<info>Nothing to clean</>');
        }

        return $this->getExitCode();
    }

    /**
     * @return void
     */
    public function bootstrap():void
    {
        $this->progressBar->setConsole($this->input, $this->output);
        $this->package->setConsole($this->input, $this->output);
        $this->package->setHttp($this->http);
        $this->package->setFilesystem($this->filesystem);
        $this->provider->setConsole($this->input, $this->output);
        $this->provider->setHttp($this->http);
        $this->provider->setFilesystem($this->filesystem);
    }

    /**
     * Flush old cached files of providers.
     *
     * @return Clean
     */
    protected function flushProviders():Clean
    {
        if (!$this->filesystem->hasFile(self::MAIN)) {
            return $this;
        }

        $providers = json_decode($this->filesystem->read(self::MAIN));
        $includes = array_keys($this->provider->normalize($providers));

        $this->initialized = $this->filesystem->hasFile(self::INIT);

        foreach ($includes as $uri) {
            $pattern = $this->filesystem->getGzName($this->shortname($uri));
            $glob = $this->filesystem->glob($pattern);

            $this->output->writeln(
                'Check old file of <info>'.
                $pattern.
                '</>'
            );

            // If not have one file or not scrumbbing
            if (!(count($glob) > 1 || $this->isScrub)) {
                continue;
            }

            $this->changed[] = $uri;
            $uri = $this->filesystem->getFullPath($this->filesystem->getGzName($uri));
            $diff = array_diff($glob, [$uri]);
            $this->removeAll($diff)->showRemoved();
        }

        return $this;
    }

    /**
     * Flush old cached files of packages.
     *
     * @return bool True if work, false otherside
     */
    protected function flushPackages():bool
    {
        $increment = 0;

        foreach ($this->changed as $uri) {
            $providers = json_decode($this->filesystem->read($uri));
            $list = $this->package->normalize($providers->providers);

            $this->output->writeln(
                '['.++$increment.'/'.count($this->changed).'] '.
                'Check old packages for provider '.
                '<info>'.$this->shortname($uri).'</>'
            );
            $this->progressBar->start(count($list));
            $this->flushPackage(array_keys($list));
            $this->progressBar->end();
            $this->output->write(PHP_EOL);
            $this->showRemoved();
        }

        return true;
    }

    /**
     * Flush from one provider.
     *
     * @param array $list List of packages
     */
    protected function flushPackage(array $list):void
    {
        $packages = $this->package->getDownloaded();

        foreach ($list as $uri) {
            $this->progressBar->progress();

            if ($this->canSkipPackage($uri, $packages)) {
                continue;
            }

            $gzName = $this->filesystem->getGzName($uri);
            $pattern = $this->shortname($gzName);
            $glob = $this->filesystem->glob($pattern);

            // If only have the file dont exist old files
            if (count($glob) < 2) {
                continue;
            }

            // Remove current value
            $fullPath = $this->filesystem->getFullPath($gzName);
            $diff = array_diff($glob, [$fullPath]);
            $this->removeAll($diff);
        }
    }

    /**
     * @param string $uri
     * @param array  $packages
     * @return bool
     */
    protected function canSkipPackage(string $uri, array $packages):bool
    {
        if ($this->initialized) {
            return true;
        }

        $folder = dirname($uri);

        // This uri was changed by last download?
        if (count($packages) && !in_array($uri, $packages)) {
            return true;
        }

        // If only have the file and link dont exist old files
        if ($this->filesystem->getCount($folder) < 3) {
            return true;
        }

        return false;
    }

    /**
     * Remove all files
     *
     * @param  array  $files
     * @return Clean
     */
    protected function removeAll(array $files):Clean
    {
        foreach ($files as $file) {
            $this->filesystem->delete($file);
        }

        $this->removed = [];
        if ($this->isVerbose()) {
            $this->removed = $files;
        }

        return $this;
    }

    /**
     * Show packages removed.
     *
     * @return Clean
     */
    protected function showRemoved():Clean
    {
        $base = getenv('PUBLIC_DIR').DIRECTORY_SEPARATOR;

        foreach ($this->removed as $file) {
            $file = str_replace($base, '', $file);
            $this->output->writeln(
                'File <fg=blue;>'.$file.'</> was removed!'
            );
        }

        $this->removed = [];

        return $this;
    }
}
