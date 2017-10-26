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
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Pool;
use stdClass;
use Generator;
use Webs\Mirror\Circular;

/**
 * Create a mirror.
 *
 * @author Webysther Nunes <webysther@gmail.com>
 */
class Create extends Base
{
    /**
     * Console description.
     *
     * @var string
     */
    protected $description = 'Create/update packagist mirror';

    /**
     * Console params configuration.
     */
    protected function configure():void
    {
        parent::configure();
        $this->setName('create')->setDescription($this->description);
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
        $this->client = new Client([
            'base_uri' => getenv('MAIN_MIRROR').'/',
            'headers' => ['Accept-Encoding' => 'gzip'],
            'decode_content' => false,
            'timeout' => 30,
            'connect_timeout' => 15,
        ]);

        $this->hasInit = false;
        $this->loadMirrors();

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

    /**
     * Load main packages.json.
     *
     * @return bool|stdClass False or the object of packages.json
     */
    protected function loadMainPackagesInformation()
    {
        $this->output->writeln(
            'Loading providers from <info>'.getenv('MAIN_MIRROR').'</>'
        );

        $response = $this->client->get('packages.json');

        // Maybe 4xx or 5xx
        if ($response->getStatusCode() >= 400) {
            $this->output->writeln('Error download source of providers');

            return false;
        }

        $json = (string) $response->getBody();
        $providers = json_decode($this->unparseGzip($json));

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->output->writeln('<error>Invalid JSON</>');

            return false;
        }

        $providers = $this->addFullPathProviders($providers);

        if (!$this->checkPackagesWasChanged($providers)) {
            return false;
        }

        if (empty($providers->{'provider-includes'})) {
            $this->output->writeln('<error>Not found providers information</>');

            return false;
        }

        return $providers;
    }

    /**
     * Check if packages.json was changed, this reduce load over main packagist.
     *
     * @return bool True if is equal, false otherside
     */
    protected function checkPackagesWasChanged($providers):bool
    {
        $cachedir = getenv('PUBLIC_DIR').'/';
        $packages = $cachedir.'packages.json.gz';
        $dotPackages = $cachedir.'.packages.json.gz';
        $newPackages = gzencode(json_encode($providers, JSON_PRETTY_PRINT));

        // if 'p/...' folder not found
        if (!file_exists($cachedir.'p')) {
            touch($cachedir.'.init');
            mkdir($cachedir.'p', 0777, true);
        }

        if (file_exists($cachedir.'.init')) {
            $this->hasInit = true;
        }

        // No provider changed? Just relax...
        if (file_exists($packages) && !$this->hasInit) {
            if (md5(file_get_contents($packages)) == md5($newPackages)) {
                $this->output->writeln('<info>Up-to-date</>');

                return false;
            }
        }

        if (!file_exists($cachedir)) {
            mkdir($cachedir, 0777, true);
        }

        if (false === file_put_contents($dotPackages, $newPackages)) {
            $this->output->writeln('<error>.packages.json not found</>');

            return false;
        }

        $this->createLink($dotPackages);

        return true;
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
    protected function downloadProviders():bool
    {
        if (!($providers = $this->loadMainPackagesInformation())) {
            return false;
        }

        $includes = count((array) $providers->{'provider-includes'});
        $this->progressBarStart($includes);

        $generator = $this->downloadProvideIncludes(
            $providers->{'provider-includes'}
        );

        if (!$generator->valid()) {
            $this->output->writeln('All providers up-to-date...');

            return true;
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
     * @param stdClass $includes Providers links
     *
     * @return Generator Providers downloaded
     */
    protected function downloadProvideIncludes(stdClass $includes):Generator
    {
        $cachedir = getenv('PUBLIC_DIR').'/';

        foreach ($includes as $template => $hash) {
            $fileurl = str_replace('%hash%', $hash->sha256, $template);
            $cachename = $cachedir.$fileurl.'.gz';

            // Only if exists
            if (file_exists($cachename) && !$this->hasInit) {
                $this->progressBarUpdate();
                continue;
            }

            yield $cachename => new Request(
                'GET',
                $fileurl,
                ['curl' => [CURLMOPT_PIPELINING => 2]]
            );
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
            $providers->$key = getenv('MAIN_MIRROR').$path;
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

    protected function loadMirrors()
    {
        $this->circular = Circular::fromArray($this->getMirrors());
    }

    protected function getMirrors():array
    {
        $mirrors = explode(',', getenv('DATA_MIRROR'));
        $mirrors[] = getenv('MAIN_MIRROR');

        return $mirrors;
    }

    /**
     * Create a simbolic link.
     *
     * @param string $path Path to file
     */
    protected function createLink(string $target):void
    {
        // From .json.gz to .json
        $link = substr($target, 0, -3);
        if (!file_exists($link)) {
            symlink(basename($target), substr($target, 0, -3));
        }
    }
}
