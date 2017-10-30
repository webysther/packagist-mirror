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
use Generator;

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
     * @var string
     */
    protected $currentProvider;

    /**
     * @var array
     */
    protected $packagesDownloaded = [];

    /**
     * @var int
     */
    const VV = OutputInterface::VERBOSITY_VERBOSE;

    /**
     * Main files
     */
    const MAIN = 'packages.json';
    const DOT = '.packages.json';
    const INIT = '.init';
    const TO = 'p';

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

        $clean = new Clean();
        $clean->addPackages($this->packagesDownloaded);
        $clean->initialize($input, $output);

        // Flush old SHA256 files
        if ($clean->flush()->stop()) {
            return $clean->getExitCode();
        }

        if ($this->initialized) {
            $this->filesystem->remove(self::INIT);
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

        $this->providers = $this->addFullPathProviders(
            $this->http->getJson(self::MAIN)
        );
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
        foreach ($providerIncludes as $name => $hash) {
            $uri = str_replace('%hash%', $hash->sha256, $name);
            $this->providerIncludes[$uri] = $hash->sha256;
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
        if (!$this->filesystem->has(self::TO)) {
            $this->filesystem->touch(self::INIT);
        }

        if ($this->filesystem->has(self::INIT)) {
            $this->initialized = true;
        }

        $newPackages = json_encode($this->providers, JSON_PRETTY_PRINT);

        // No provider changed? Just relax...
        if ($this->canSkip(self::MAIN)) {
            $old = $this->filesystem->hashFile(self::MAIN);
            $new = $this->filesystem->hash($newPackages);

            if ($old == $new) {
                $this->output->writeln(self::MAIN.' <info>updated</>');
                $this->generateHtml();
                $this->setExitCode(0);
                return true;
            }
        }

        $this->filesystem->write(self::DOT, $newPackages);
        return false;
    }

    /**
     * Switch current packagist.json to space and .packagist to packagist.json.
     *
     * @return bool True if work, false otherside
     */
    protected function switch():Create
    {
        // If .packages.json dont exists
        if (!$this->filesystem->has(self::DOT)) {
            return $this;
        }

        // Move to new location
        $this->filesystem->move(self::DOT, self::MAIN);
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

        $generator = $this->loadProviderIncludes()->getProvidersGenerator();

        if (empty(iterator_to_array($generator))) {
            $this->output->writeln('All providers are <info>updated</>');
            return $this->setExitCode(0);
        }

        $this->progressBar->start(count($this->providerIncludes));

        $this->http->pool(
            $generator,
            // Success
            function ($body, $path) {
                $this->filesystem->write($path, $body);
            },
            // If complete, even failed and success
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
        foreach ($this->providerIncludes as $uri => $hash) {
            $path = $this->filesystem->normalize($uri);

            // If exists and not initial download
            if ($this->canSkip($path)) {
                $this->progressBar->progress();
                continue;
            }

            yield $path => $this->http->getRequest($uri);
        }
    }

    /**
     * @param  string $path
     * @return bool
     */
    protected function canSkip(string $path):bool
    {
        if ($this->filesystem->has($path) && !$this->initialized) {
            return true;
        }

        return false;
    }

    /**
     * Show errors.
     *
     * @return Create
     */
    protected function showErrors():Create
    {
        if ($this->output->getVerbosity() < Create::VV){
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
                "<comment>$shortname</> failed from ".
                "<info>$host</> with HTTP error"
            );

            if (!$error) {
                $this->output->writeln(
                    ':'.PHP_EOL.'<error>'.$reason->getMessage().'</>'
                );
                continue;
            }

            $this->output->writeln(" <error>$error</>");
        }

        $this->output->write(PHP_EOL);
        return $this;
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

        return $this;
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
     * @param  string $uri
     * @return Create
     */
    protected function loadProviderPackages(string $uri):Create
    {
        $providers = $this->filesystem->readJson($uri)->providers;
        $this->currentProvider = $uri;

        $this->providerPackages = [];
        foreach ($providers as $name => $hash) {
            $uri = sprintf('p/%s$%s.json', $name, $hash->sha256);
            $this->providerPackages[$uri] = $hash->sha256;
        }


        return $this;
    }

    /**
     * Download packages listed on provider-*.json on public/p dir.
     *
     * @return Create
     */
    protected function downloadPackages():Create
    {
        $totalProviders = count($this->providerIncludes);
        $currentProvider = 0;

        foreach ($this->providerIncludes as $uri => $hash) {
            $shortname = $this->shortname($uri);

            $this->output->writeln(
                '['.++$currentProvider.'/'.$totalProviders.']'.
                ' Loading packages from <info>'.$shortname.'</> provider'
            );

            $generator = $this->loadProviderPackages($uri)->getPackagesGenerator();
            if (empty(iterator_to_array($generator))) {
                continue;
            }

            $this->progressBar->start(count($this->providerPackages));
            $this->poolPackages($generator);
            $this->progressBar->end();
            $this->showErrors()->disableDueErrors()->fallback();
        }

        return $this;
    }

    /**
     * Download only a package.
     *
     * @return Generator Providers downloaded
     */
    protected function getPackagesGenerator():Generator
    {
        foreach ($this->providerPackages as $uri => $hash) {
            $path = $this->filesystem->normalize($uri);

            // Only if exists
            if ($this->filesystem->has($path)) {
                $this->progressBar->progress();
                continue;
            }

            if ($this->initialized) {
                $uri = $this->http->getMirror()->getNext().'/'.$uri;
            }

            yield $path => $this->http->getRequest($uri);
        }
    }

    /**
     * @param  Generator    $generator
     * @param  bool|boolean $useMirrors
     * @return Create
     */
    protected function poolPackages(Generator $generator, bool $useMirrors = true):Create
    {
        if($useMirrors){
            $this->http->useMirrors();
        }

        $this->http->pool(
            $generator,
            // Success
            function ($body, $path) {
                $this->filesystem->write($path, $body);
                $this->packagesDownloaded[] = $path;
            },
            // If complete, even failed and success
            function () {
                $this->progressBar->progress();
            }
        );

        return $this;
    }

    protected function fallback():Create
    {
        $total = count($this->http->getPoolErrors());

        if (!$total) {
            return $this;
        }

        $shortname = $this->shortname($this->currentProvider);

        $this->output->writeln(
            'Fallback packages from <info>'.$shortname.
            '</> provider to main mirror <info>'.$this->http->getBaseUri().'</>'
        );

        $this->providerPackages = $this->http->getPoolErrors();
        $generator = $this->getPackagesGenerator();
        $this->progressBar->start($total);
        $this->poolPackages($generator, false);
        $this->progressBar->end();
        $this->showErrors();
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
