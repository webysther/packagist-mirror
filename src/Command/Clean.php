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
use FilesystemIterator;
use stdClass;

/**
 * Clean mirror outdated files.
 *
 * @author Webysther Nunes <webysther@gmail.com>
 */
class Clean extends Base
{
    /**
     * Console description.
     *
     * @var string
     */
    protected $description = 'Clean outdated files of mirror';

    /**
     * Packages to verify first.
     *
     * @var array
     */
    protected $packages = [];

    /**
     * Console params configuration.
     */
    protected function configure():void
    {
        parent::configure();
        $this->setName('clean')
             ->setDescription($this->description)
             ->addOption(
                 'scrub',
                 null,
                 InputOption::VALUE_NONE,
                 'Check all directories for old files, use only to check all disk'
             );
    }

    /**
     * Execution.
     *
     * @param InputInterface  $input  Input console
     * @param OutputInterface $output Output console
     *
     * @return int 0 if pass, any another is error
     */
    public function childExecute(InputInterface $input, OutputInterface $output):int
    {
        if (!$this->flush($input, $output)) {
            return 1;
        }

        if (!count($this->changed)) {
            $output->writeln('Nothing to clean');
        }

        return 0;
    }

    /**
     * Flush old files.
     *
     * @param InputInterface  $input  Input console
     * @param OutputInterface $output Output console
     *
     * @return bool True if work, false otherside
     */
    public function flush(InputInterface $input, OutputInterface $output):bool
    {
        $this->input = $input;
        $this->output = $output;

        if (!$this->flushProviders()) {
            return false;
        }

        if (!$this->flushPackages()) {
            return false;
        }

        return true;
    }

    /**
     * Add information about how package is checked.
     *
     * @param array $list List of name packages
     */
    public function setChangedPackage(array $list):void
    {
        $this->packages = $list;
    }

    /**
     * Flush old cached files of providers.
     *
     * @return bool True if work, false otherside
     */
    protected function flushProviders():bool
    {
        $cachedir = getenv('PUBLIC_DIR').'/';
        $packages = $cachedir.'packages.json.gz';

        $json = gzdecode(file_get_contents($packages));
        $providers = json_decode($json);
        $includes = $providers->{'provider-includes'};
        $this->changed = [];

        $scrub = false;
        if ($this->input->hasOption('scrub') && $this->input->getOption('scrub')) {
            $scrub = true;
        }

        foreach ($includes as $template => $hash) {
            $fileurl = $cachedir.str_replace('%hash%', '*', $template).'.gz';
            $glob = glob($fileurl, GLOB_NOSORT);

            $this->output->writeln(
                'Check old file of <info>'.
                $fileurl.
                '</>'
            );

            // If have files and more than 1 to exists old ones
            if (count($glob) > 1 || $scrub) {
                $fileurlCurrent = $cachedir;
                $fileurlCurrent .= str_replace(
                    '%hash%',
                    $hash->sha256,
                    $template
                ).'.gz';

                $this->changed[] = $fileurlCurrent;

                foreach ($glob as $file) {
                    if ($file == $fileurlCurrent) {
                        continue;
                    }

                    $this->output->writeln(
                        'Old file <fg=blue;>'.$file.'</> was removed!'
                    );
                    unlink($file);
                }
            }
        }

        return true;
    }

    /**
     * Flush old cached files of packages.
     *
     * @return bool True if work, false otherside
     */
    protected function flushPackages():bool
    {
        $increment = 0;

        foreach ($this->changed as $urlProvider) {
            $provider = json_decode(gzdecode(file_get_contents($urlProvider)));
            $list = $provider->providers;
            $total = count((array) $list);
            ++$increment;

            $this->output->writeln(
                '['.$increment.'/'.count($this->changed).'] '.
                'Check old packages for provider '.
                '<info>'.$this->shortname($urlProvider).'</>'
            );
            $this->progressBarStart($total);
            $this->flushPackage($list);
            $this->progressBarFinish();
        }

        return true;
    }

    /**
     * Flush from one provider.
     *
     * @param stdClass $list List of packages
     */
    protected function flushPackage(stdClass $list):void
    {
        $cachedir = getenv('PUBLIC_DIR').'/';
        $uri = $cachedir.'p/%s$%s.json.gz';

        foreach ($list as $name => $hash) {
            $this->progressBarUpdate();

            if (file_exists($cachedir.'.init')) {
                continue;
            }

            $folder = $cachedir.'p/'.dirname($name);

            // This folder was changed by last download?
            if (count($this->packages) && !in_array($folder, $this->packages)) {
                continue;
            }

            $fi = new FilesystemIterator(
                $cachedir.'p/'.dirname($name),
                FilesystemIterator::SKIP_DOTS
            );

            // If only have the file dont exist old files
            if (iterator_count($fi) < 2) {
                continue;
            }

            $fileurlCurrent = sprintf($uri, $name, $hash->sha256);
            $fileurl = sprintf($uri, $name, '*');
            $glob = glob($fileurl, GLOB_NOSORT);

            // If have files and more than 1 to exists old ones
            if (count($glob) > 1) {
                foreach ($glob as $file) {
                    if ($file == $fileurlCurrent) {
                        continue;
                    }

                    unlink($file);
                }
            }
        }
    }
}
