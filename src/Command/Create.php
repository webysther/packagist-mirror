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
use Webs\Mirror\ShortName;
use stdClass;

/**
 * Create a mirror.
 *
 * @author Webysther Nunes <webysther@gmail.com>
 */
class Create extends Base
{
    use ShortName;

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
    protected $providerIncludes;

    /**
     * @var array
     */
    protected $packages = [];

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
        if ($this->downloadProviders()->stop()) {
            return $this->exitCode;
        }

        // Download packages
        if ($this->downloadPackages()->stop()) {
            return $this->exitCode;
        }

        // Switch .packagist.json to packagist.json
        if ($this->switch()->stop()) {
            return $this->exitCode;
        }

        // Flush old SHA256 files
        $clean = new Clean();
        $clean->addPackages($this->packages);
        $clean->initialize($input, $output);
        if ($clean->flush()->stop()) {
            return $clean->getExitCode();
        }

        if ($this->initialized) {
            $this->filesystem->remove('.init');
        }

        return $this->generateHtml()->exitCode;
    }

    /**
     * Load main packages.json.
     *
     * @return Create
     */
    protected function loadPackagesJson():Create
    {
        $this->output->writeln(
            'Loading providers from <info>'.$this->http->getBaseUri().'</>'
        );

        $json = $this->http->getJson('packages.json');
        $this->providers = $this->addFullPathProviders($json);
        return $this;
    }

    /**
     * Load provider includes
     *
     * @return Create
     */
    protected function loadProviderIncludes():Create
    {
        if (!property_exists($this->providers, 'provider-includes')) {
            throw new Exception("Not found providers information", 1);
        }

        $providerIncludes = $this->providers->{'provider-includes'};

        $this->providerIncludes = [];
        foreach ($this->providerIncludes as $name => $hash) {
            $uri = str_replace('%hash%', $hash->sha256, $name);
            $this->providerIncludes[$hash->sha256] = $uri;
        }


        return $this;
    }

    /**
     * Check if packages.json was changed
     *
     * @return boolean
     */
    protected function isEqual():bool
    {
        // if 'p/...' folder not found
        if (!$this->filesystem->has('p')) {
            $this->filesystem->touch('.init');
        }

        if ($this->filesystem->has('.init')) {
            $this->initialized = true;
        }

        $newPackages = json_encode($this->providers, JSON_PRETTY_PRINT);
        $file = 'packages.json.gz';
        $dot = '.'.$file;

        // No provider changed? Just relax...
        if ($this->filesystem->has($file) && !$this->initialized) {
            $old = $this->filesystem->hashFile($file);
            $new = $this->filesystem->hash($newPackages);

            if ($old == $new) {
                $this->output->writeln($file.' <info>updated</>');
                $this->setExitCode(0);
                return true;
            }
        }

        $this->filesystem->write($dot, $newPackages);
        return false;
    }

    /**
     * Switch current packagist.json to space and .packagist to packagist.json.
     *
     * @return bool True if work, false otherside
     */
    protected function switch():Create
    {
        $old = 'packages.json.gz';
        $new = '.'.$old;

        if (!$this->filesystem->has($new)) {
            return $this;
        }

        $link = substr($old, 0, -3);
        $this->move($new, $old);
        $this->symlink($old, $link);
        return $this;
    }

    /**
     * Download packages.json & provider-xxx$xxx.json.
     *
     * @return bool True if work, false otherside
     */
    protected function downloadProviders():Create
    {
        if ($this->loadPackagesJson()->isEqual()) {
            return $this;
        }

        $this->loadProviderIncludes();
        $this->progressBar->start(count($this->providerIncludes));

        $generator = $this->getProvidersGenerator();

        if (empty(iterator_to_array($generator))) {
            $this->output->writeln('All providers are <info>updated</>');
            return $this->setExitCode(0);
        }

        $this->http->pool(
            $generator,
            function ($body, $path) {
                $this->filesystem->write($path, $body);
                $this->filesystem->symlink($path, substr($path, 0, -3));
            },
            function () {
                $this->progressBar->progress();
            }
        );

        $this->progressBar->end();
        $this->showErrors();
        return $this;
    }

    /**
     * Download packages.json & provider-xxx$xxx.json.
     *
     * @return Generator Providers downloaded
     */
    protected function getProvidersGenerator():Generator
    {
        foreach ($this->providerIncludes as $hash => $uri) {
            $path = $this->filesystem->normalize($uri);

            // Only if exists
            if ($this->filesystem->has($path) && !$this->initialized) {
                $this->progressBarUpdate();
                continue;
            }

            yield $path => $this->http->getRequest($uri);
        }
    }

    /**
     * Show errors.
     *
     * @return Create
     */
    protected function showErrors():Create
    {
        if ($this->output->getVerbosity() < OutputInterface::VERBOSITY_VERBOSE){
            return $this;
        }

        $errors = $this->http->getPoolErrors();
        if (count($errors) === 0) {
            return $this;
        }

        foreach ($errors as $path => $reason) {
            $shortname = $this->shortname($path);
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
    protected function disableDueErrors()
    {
        $errors = $this->http->getPoolErrors();
        if (count($errors) === 0) {
            return $this;
        }

        $counter = [];

        foreach ($errors as $reason) {
            $uri = $reason->getRequest()->getUri();
            $host = $uri->getScheme().'://'.$uri->getHost();

            if (!isset($counter[$host])) {
                $counter[$host] = 0;
            }

            $counter[$host]++;
        }

        $mirrors = $this->http->getMirror()->toArray();

        foreach ($mirrors as $mirror) {
            $total = $counter[$mirror];
            if ($total < 1000) {
                continue;
            }

            $this->output->write(PHP_EOL);
            $this->output->writeln(
                '<error>Due to '.
                $total.' errors mirror '.
                $mirror.' will be disabled</>'
            );
            $this->output->write(PHP_EOL);
            $this->http->getMirror()->remove($mirror);
        }
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
     * Generate HTML of index.html.
     */
    protected function generateHtml():Create
    {
        ob_start();
        include getcwd().'/resources/index.html.php';
        $this->filesystem->write('index.html', ob_get_clean());
        return $this;
    }
}
