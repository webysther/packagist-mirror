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
use Symfony\Component\Console\Output\OutputInterface;
use stdClass;

/**
 * Create a mirror.
 *
 * @author Webysther Nunes <webysther@gmail.com>
 */
class Create extends Base
{
    /**
     * @var boolean
     */
    protected $initialized = false;

    /**
     * @var stdClass
     */
    protected $providers;

    /**
     * @var array
     */
    protected $includes;

    /**
     * {@inheritdoc}
     */
    public function __construct($name = '')
    {
        parent::__construct('create');
        $this->setDescription(
            'Create/update packagist mirror'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output):int
    {
        $this->progressBar->addConsole($input, $output);

        // Download providers, with repository, is incremental
        if (!$this->downloadProviders()) {
            return 1;
        }

        // Download packages
        if (!$this->downloadPackages()) {
            return 1;
        }

        // Switch .packagist.json to packagist.json
        if (!$this->switch()) {
            return 1;
        }

        // Flush old SHA256 files
        $clean = new Clean();
        if (isset($this->packages) && count($this->packages)) {
            $clean->setChangedPackage($this->packages);
        }

        if (!$clean->flush($input, $output)) {
            return 1;
        }

        if ($this->hasInit) {
            unlink(getenv('PUBLIC_DIR').'/.init');
        }

        $this->generateHtml();

        return 0;
    }

    protected function setExitCode(int $exit):Create
    {
        $this->exitCode = $exit;
        return $this;
    }

    /**
     * Load main packages.json.
     *
     * @return bool|stdClass False or the object of packages.json
     */
    protected function loadMainPackagesInformation()
    {
        $this->output->writeln(
            'Loading providers from <info>'.$this->http->getBaseUri().'</>'
        );

        $json = $this->http->getJson('packages.json');
        $this->providers = $this->addFullPathProviders($json);

        if ($this->isEqual($providers)) {
            return true;
        }

        return false;
    }

    protected function getIncludes()
    {
        if(!empty($this->includes)){
            return $this->includes;
        }

        $this->includes = (array) $this->providers->{'provider-includes'};

        if (!array_count_values($this->includes)) {
            throw new Exception("Not found providers information", 1);
        }

        return $this->includes;
    }

    /**
     * Check if packages.json was changed
     *
     * @param  stdClass $providers
     * @return boolean
     */
    protected function isEqual(stdClass $providers):bool
    {
        // if 'p/...' folder not found
        if (!$this->filesystem->has('p')) {
            $this->filesystem->touch('.init');
        }

        if ($this->filesystem->has('.init')) {
            $this->initialized = true;
        }

        $newPackages = json_encode($providers, JSON_PRETTY_PRINT);

        // No provider changed? Just relax...
        if ($this->filesystem->has('packages.json.gz') && !$this->initialized) {
            $old = $this->filesystem->hashFile('packages.json.gz');
            $new = $this->filesystem->hash($newPackages);

            if ($old == $new) {
                $this->output->writeln('<info>Up-to-date</>');
                return $this->setExitCode(0);
            }
        }

        $this->filesystem->write('.packages.json.gz', $newPackages);
        return $this;
    }

    /**
     * Switch current packagist.json to space and .packagist to packagist.json.
     *
     * @return bool True if work, false otherside
     */
    protected function switch()
    {
        $cachedir = getenv('PUBLIC_DIR').'/';
        $packages = $cachedir.'packages.json.gz';
        $dotPackages = $cachedir.'.packages.json.gz';

        if (file_exists($dotPackages)) {
            if (file_exists($packages)) {
                $this->output->writeln(
                    '<comment>Removing old packages.json</>'
                );
                unlink($packages);
            }

            $this->output->writeln(
                'Switch <info>.packages.json</> to <info>packages.json</>'
            );
            copy($dotPackages, $packages);
            $this->createLink($packages);
        }

        return true;
    }

    /**
     * Download packages.json & provider-xxx$xxx.json.
     *
     * @return bool True if work, false otherside
     */
    protected function downloadProviders():Create
    {
        if ($this->loadMainPackagesInformation()) {
            return $this;
        }

        $this->progressBar->start(
            array_count_values($this->getIncludes())
        );

        $generator = $this->getProvidersGenerator();

        if (!$generator->valid()) {
            $this->output->writeln('All providers up-to-date...');

            return $this;
        }

        $this->errors = [];
        $this->providers = [];
        $pool = new Pool($this->client, $generator, [
            'concurrency' => getenv('MAX_CONNECTIONS'),
            'fulfilled' => function ($response, $name) {
                $json = (string) $response->getBody();
                file_put_contents($name, $json);
                $this->createLink($name);
                $this->providers[$name] = json_decode($this->unparseGzip($json));
                $this->progressBarUpdate();
            },
            'rejected' => function ($reason, $name) {
                $this->errors[$name] = $reason;
                $this->progressBarUpdate();
            },
        ]);

        // Initiate the transfers and create a promise
        $promise = $pool->promise();

        // Force the pool of requests to complete.
        $promise->wait();

        $this->progressBarUpdate();
        $this->progressBarFinish();
        $this->showErrors($this->errors);

        return true;
    }

    /**
     * Download packages.json & provider-xxx$xxx.json.
     *
     * @return Generator Providers downloaded
     */
    protected function getProvidersGenerator():Generator
    {
        $includes = $this->getIncludes();

        foreach ($includes as $template => $hash) {
            $uri = str_replace('%hash%', $hash->sha256, $template);
            $file = $this->normalize($uri);

            // Only if exists
            if ($this->filesystem->has($file) && !$this->initialized) {
                $this->progressBarUpdate();
                continue;
            }

            yield $file => $this->http->getRequest($uri);
        }
    }

    /**
     * Show errors formatted.
     *
     * @param array $errors Errors
     */
    protected function showErrors(array $errors):void
    {
        if (!count($errors) || $this->output->getVerbosity() < OutputInterface::VERBOSITY_VERBOSE) {
            return;
        }

        foreach ($errors as $name => $reason) {
            $shortname = $this->shortname($name);
            $error = $reason->getCode();
            $host = $reason->getRequest()->getUri()->getHost();

            $this->output->write(
                "<comment>$shortname</> failed from <info>$host</> with HTTP error"
            );

            if (!$error) {
                $this->output->writeln(
                    ':'.PHP_EOL.'<error>'.$reason->getMessage().'</>'
                );
                continue;
            }

            $this->output->writeln(" <error>$error</>");
        }

        $this->output->writeln('');
    }

    /**
     * Disable mirror when due lots of errors.
     */
    protected function disableDueErrors(array $errors)
    {
        if (!count($errors)) {
            return;
        }

        $counter = [];

        foreach ($errors as $reason) {
            $uri = $reason->getRequest()->getUri();
            $host = $uri->getScheme().'://'.$uri->getHost();

            if (!isset($counter[$host])) {
                $counter[$host] = 0;
            }

            ++$counter[$host];
        }

        $mirrors = $this->circular->toArray();
        $circular = [];

        foreach ($mirrors as $mirror) {
            if ($counter[$mirror] > 1000) {
                $this->output->writeln(
                    PHP_EOL
                    .'<error>Due to '.$counter[$mirror].' errors mirror '.$mirror.' will be disabled</>'.
                    PHP_EOL
                );
                continue;
            }

            $circular[] = $mirror;
        }

        putenv('DATA_MIRROR='.implode(',', $circular));
        $this->loadMirrors();
    }

    protected function fallback(array $files, stdClass $list, string $provider):void
    {
        $total = count($files);

        if (!$total) {
            return;
        }

        $circular = $this->circular;
        $this->circular = Circular::fromArray([getenv('MAIN_MIRROR')]);

        $shortname = $this->shortname($provider);

        $this->output->writeln(
            'Fallback packages from <info>'.$shortname.
            '</> provider to main mirror <info>'.getenv('MAIN_MIRROR').'</>'
        );

        $generator = $this->downloadPackage($list);
        $this->progressBarStart($total);
        $this->errors = [];

        $pool = new Pool($this->client, $generator, [
            'concurrency' => getenv('MAX_CONNECTIONS'),
            'fulfilled' => function ($response, $name) {
                $gzip = (string) $response->getBody();
                file_put_contents($name, $this->parseGzip($gzip));
                $this->createLink($name);
                $this->packages[] = dirname($name);
                $this->progressBarUpdate();
            },
            'rejected' => function ($reason, $name) {
                $this->errors[$name] = $reason;
                $this->progressBarUpdate();
            },
        ]);

        $promise = $pool->promise();

        // Force the pool of requests to complete.
        $promise->wait();

        $this->progressBarUpdate();
        $this->progressBarFinish();
        $this->showErrors($this->errors);
        $this->packages = array_unique($this->packages);
        $this->circular = $circular;
    }

    /**
     * Add base url of packagist.org to services on packages.json of
     * mirror don't support.
     *
     * @param stdClass $providers List of providers from packages.json
     */
    protected function addFullPathProviders(stdClass $providers):stdClass
    {
        // Add full path for services of mirror don't provide only packagist.org
        foreach (['notify', 'notify-batch', 'search'] as $key) {
            // Just in case packagist.org add full path in future
            $path = parse_url($providers->$key){'path'};
            $providers->$key = $this->http->getBaseUri().$path;
        }

        return $providers;
    }

    /**
     * Download packages listed on provider-*.json on public/p dir.
     *
     * @return bool True if work, false otherside
     */
    protected function downloadPackages():bool
    {
        if (!isset($this->providers)) {
            return true;
        }

        $this->packages = [];

        $totalProviders = count($this->providers);
        $currentProvider = 0;
        foreach ($this->providers as $provider => $packages) {
            ++$currentProvider;
            $list = $packages->providers;
            $total = count((array) $list);
            $shortname = $this->shortname($provider);

            $this->output->writeln(
                '['.$currentProvider.'/'.$totalProviders.']'.
                ' Loading packages from <info>'.$shortname.'</> provider'
            );

            $generator = $this->downloadPackage($list);
            $this->progressBarStart($total);
            $this->errors = [];

            $pool = new Pool($this->client, $generator, [
                'concurrency' => getenv('MAX_CONNECTIONS') * $this->circular->count(),
                'fulfilled' => function ($response, $name) {
                    $gzip = (string) $response->getBody();
                    file_put_contents($name, $this->parseGzip($gzip));
                    $this->createLink($name);
                    $this->packages[] = dirname($name);
                    $this->progressBarUpdate();
                },
                'rejected' => function ($reason, $name) {
                    $this->errors[$name] = $reason;
                    $this->progressBarUpdate();
                },
            ]);

            // Initiate the transfers and create a promise
            $promise = $pool->promise();

            // Force the pool of requests to complete.
            $promise->wait();

            $this->progressBarUpdate();
            $this->progressBarFinish();
            $this->showErrors($this->errors);
            $this->disableDueErrors($this->errors);
            $this->fallback($this->errors, $list, $provider);
            $this->packages = array_unique($this->packages);
        }

        return true;
    }

    /**
     * Download only a package.
     *
     * @param stdClass $list Packages links
     *
     * @return Generator Providers downloaded
     */
    protected function downloadPackage(stdClass $list):Generator
    {
        $cachedir = getenv('PUBLIC_DIR').'/';
        $uri = 'p/%s$%s.json';

        foreach ($list as $name => $hash) {
            $fileurl = sprintf($uri, $name, $hash->sha256);
            $cachename = $cachedir.$fileurl.'.gz';

            // Only if exists
            if (file_exists($cachename)) {
                $this->progressBarUpdate();
                continue;
            }

            // if 'p/...' folder not found
            $subdir = dirname($cachename);
            if (!file_exists($subdir)) {
                mkdir($subdir, 0777, true);
            }

            if ($this->hasInit) {
                $fileurl = $this->circular->current().'/'.$fileurl;
                $this->circular->next();
            }

            yield $cachename => new Request(
                'GET',
                $fileurl,
                ['curl' => [CURLMOPT_PIPELINING => 2]]
            );
        }
    }

    /**
     * Generate HTML of index.html.
     */
    protected function generateHtml():void
    {
        ob_start();
        include getcwd().'/resources/index.html.php';
        file_put_contents(getenv('PUBLIC_DIR').'/index.html', ob_get_clean());
    }
}
