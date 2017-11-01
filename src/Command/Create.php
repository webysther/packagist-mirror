<?php

declare(strict_types = 1);

/*
 * This file is part of the Packagist Mirror.
 *
 * For the full license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Webs\Mirror\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Webs\Mirror\ShortName;
use Webs\Mirror\Provider;
use stdClass;
use Generator;
use Closure;

/**
 * Create a mirror.
 *
 * @author Webysther Nunes <webysther@gmail.com>
 */
class Create extends Base
{
    use ShortName;

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
    protected $providerPackages;

    /**
     * @var Clean
     */
    protected $clean;

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
        $this->progressBar->setConsole($input, $output);
        $this->package->setConsole($input, $output);
        $this->package->setHttp($this->http);
        $this->provider->setConsole($input, $output);
        $this->provider->setHttp($this->http);

        // Download providers, with repository, is incremental
        if ($this->downloadProviders()->stop()) {
            return $this->getExitCode();
        }

        // Download packages
        if ($this->downloadPackages()->stop()) {
            return $this->getExitCode();
        }

        // Move to new location
        $this->filesystem->move(self::DOT);

        // Clean
        $this->setExitCode($this->clean->execute($input, $output));

        if ($this->initialized) {
            $this->filesystem->delete(self::INIT);
        }

        return $this->getExitCode();
    }

    /**
     * @param Clean $clean
     */
    public function setClean(Clean $clean):Create
    {
        $this->clean = $clean;

        return $this;
    }

    /**
     * @return int
     */
    protected function getExitCode():int
    {
        $this->generateHtml();

        return isset($this->exitCode) ? $this->exitCode : 0;
    }

    /**
     * Check if packages.json was changed.
     *
     * @return bool
     */
    protected function isEqual():bool
    {
        // if 'p/...' folder not found
        if (!$this->filesystem->has(self::TO)) {
            $this->filesystem->touch(self::INIT);
        }

        $this->initialized = $this->filesystem->hasFile(self::INIT);

        $newPackages = json_encode($this->providers, JSON_PRETTY_PRINT);

        // No provider changed? Just relax...
        if ($this->canSkip(self::MAIN)) {
            $old = $this->filesystem->getHashFile(self::MAIN);
            $new = $this->filesystem->getHash($newPackages);

            if ($old == $new) {
                $this->output->writeln(self::MAIN.' <info>updated</>');
                $this->setExitCode(0);

                return true;
            }
        }

        $this->filesystem->write(self::DOT, $newPackages);

        return false;
    }

    /**
     * Download packages.json & provider-xxx$xxx.json.
     *
     * @return Create
     */
    protected function downloadProviders():Create
    {
        $this->output->writeln(
            'Loading providers from <info>'.$this->http->getBaseUri().'</>'
        );

        $this->providers = $this->provider->addFullPath(
            $this->package->loadMainJson()
        );


        if ($this->isEqual()) {
            return $this;
        }

        $this->providerIncludes = $this->provider->normalize($this->providers);
        $generator = $this->getProvidersGenerator();

        $this->progressBar->start(count($this->providerIncludes));

        $success = function($body, $path) {
            $this->filesystem->write($path, $body);
        };

        $this->http->pool($generator, $success, $this->getClosureComplete());
        $this->progressBar->end();
        $this->showErrors();

        if ($generator->getReturn() && !$this->initialized) {
            $this->output->writeln('All providers are <info>updated</>');

            return $this->setExitCode(0);
        }

        return $this;
    }

    /**
     * Download packages.json & provider-xxx$xxx.json.
     *
     * @return Generator Providers downloaded
     */
    protected function getProvidersGenerator():Generator
    {
        $providerIncludes = array_keys($this->providerIncludes);
        $updated = true;
        foreach ($providerIncludes as $uri) {
            if ($this->filesystem->has($uri)) {
                continue;
            }

            $updated = false;
            yield $uri => $this->http->getRequest($uri);
        }

        return $updated;
    }

    /**
     * @param string $path
     *
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
        $errors = $this->http->getPoolErrors();

        if (!$this->isVerbose() || !$errors) {
            return $this;
        }

        $rows = [];
        foreach ($errors as $path => $reason) {
            list('code' => $code, 'host' => $host, 'message' => $message) = $reason;

            $error = $code;
            if (!$error) {
                $error = $message;
            }

            $rows[] = [
                '<info>'.$host.'</>',
                '<comment>'.$this->shortname($path).'</>',
                '<error>'.$error.'</>'
            ];
        }

        $table = new Table($this->output);
        $table->setHeaders(['Mirror', 'Path', 'Error']);
        $table->setRows($rows);
        $table->render();

        return $this;
    }

    /**
     * Disable mirror when due lots of errors.
     */
    protected function disableDueErrors()
    {
        $mirrors = $this->http->getMirror()->toArray();

        foreach ($mirrors as $mirror) {
            $total = $this->http->getTotalErrorByMirror($mirror);
            if($total < 1000){
                continue;
            }

            $this->output->write(PHP_EOL);
            $this->output->writeln(
                'Due to <error>'.$total.
                ' errors</> mirror <comment>'.
                $mirror.'</> will be disabled'
            );
            $this->output->write(PHP_EOL);
            $this->http->getMirror()->remove($mirror);
        }

        return $this;
    }

    /**
     * @param string $uri
     *
     * @return Create
     */
    protected function loadProviderPackages(string $uri):Create
    {
        $providers = json_decode($this->filesystem->read($uri))->providers;
        $this->providerPackages = $this->package->normalize($providers);
        $this->currentProvider = $uri;

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

        $providerIncludes = array_keys($this->providerIncludes);
        foreach ($providerIncludes as $uri) {
            $shortname = $this->shortname($uri);

            $this->output->writeln(
                '['.++$currentProvider.'/'.$totalProviders.']'.
                ' Loading packages from <info>'.$shortname.'</> provider'
            );

            $this->http->useMirrors();
            $generator = $this->loadProviderPackages($uri)->getPackagesGenerator();
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
        $providerPackages = array_keys($this->providerPackages);
        foreach ($providerPackages as $uri) {
            if ($this->filesystem->has($uri)) {
                continue;
            }

            yield $uri => $this->http->getRequest($uri);
        }
    }

    /**
     * @param Generator $generator
     * @param bool|bool $useMirrors
     *
     * @return Create
     */
    protected function poolPackages(Generator $generator):Create
    {
        $this->http->pool(
            $generator,
            // Success
            function($body, $path) {
                $this->filesystem->write($path, $body);
                $this->package->setDownloaded($path);
            },
            // If complete, even failed and success
            $this->getClosureComplete()
        );

        return $this;
    }

    /**
     * @return Closure
     */
    protected function getClosureComplete():Closure
    {
        return function() {
            $this->progressBar->progress();
        };
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
        $this->poolPackages($generator);
        $this->progressBar->end();
        $this->showErrors();

        return $this;
    }

    /**
     * Generate HTML of index.html.
     */
    protected function generateHtml():Create
    {
        ob_start();
        include getcwd().'/resources/index.html.php';
        file_put_contents('index.html', ob_get_clean());

        return $this;
    }
}
