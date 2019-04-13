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
use Symfony\Component\Console\Helper\Table;
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
        $this->initialize($input, $output);
        $this->bootstrap();

        // Download providers
        $this->downloadProviders();

        // Download packages
        if ($this->stop() || $this->downloadPackages()->stop()) {
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

        return parent::getExitCode();
    }

    /**
     * Check if packages.json was changed.
     *
     * @return bool
     */
    protected function isEqual():bool
    {
        // if 'p/...' folder not found
        if (!is_dir($this->filesystem->getFullPath(self::TO))) {
            $this->filesystem->touch(self::INIT);
            $this->moveToPublic();
        }

        $this->initialized = $this->filesystem->hasFile(self::INIT);

        $newPackages = json_encode($this->providers, JSON_PRETTY_PRINT);

        // No provider changed? Just relax...
        if ($this->filesystem->has(self::MAIN) && !$this->initialized) {
            $old = $this->filesystem->getHashFile(self::MAIN);
            $new = $this->filesystem->getHash($newPackages);

            if ($old == $new) {
                $this->output->writeln(self::MAIN.' <info>updated</>');
                $this->setExitCode(0);

                return true;
            }
        }

        if (!$this->filesystem->has(self::MAIN)) {
            $this->initialized = true;
        }

        $this->provider->setInitialized($this->initialized);
        $this->filesystem->write(self::DOT, $newPackages);

        return false;
    }

    /**
     * Copy all public resources to public
     *
     * @return void
     */
    protected function moveToPublic():void
    {
        $from = getcwd().'/resources/public/';
        foreach (new \DirectoryIterator($from) as $fileInfo) {
            if($fileInfo->isDot()) continue;
            $file = $fileInfo->getFilename();
            $to = $this->filesystem->getFullPath($file);
            copy($from.$file, $to);
        }
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
            $this->package->getMainJson()
        );

        if ($this->isEqual()) {
            return $this;
        }

        $this->providerIncludes = $this->provider->normalize($this->providers);
        $generator = $this->provider->getGenerator($this->providerIncludes);

        $this->progressBar->start(count($this->providerIncludes));

        $success = function ($body, $path) {
            $this->provider->setDownloaded($path);
            $this->filesystem->write($path, $body);
        };

        $this->http->pool($generator, $success, $this->getClosureComplete());
        $this->progressBar->end();
        $this->showErrors();

        // If initialized can have provider downloaded by half
        if ($generator->getReturn() && !$this->initialized) {
            $this->output->writeln('All providers are <info>updated</>');

            return $this->setExitCode(0);
        }

        return $this;
    }

    /**
     * Show errors.
     *
     * @return Create
     */
    protected function showErrors():Create
    {
        $errors = $this->http->getPoolErrors();

        if (!$this->isVerbose() || empty($errors)) {
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
                '<error>'.$error.'</>',
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
            if ($total < 1000) {
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
     * Download packages listed on provider-*.json on public/p dir.
     *
     * @return Create
     */
    protected function downloadPackages():Create
    {
        $providerIncludes = $this->provider->getDownloaded();
        $totalProviders = count($providerIncludes);

        foreach ($providerIncludes as $counter => $uri) {
            $this->currentProvider = $uri;
            $shortname = $this->shortname($uri);

            ++$counter;
            $this->output->writeln(
                '['.$counter.'/'.$totalProviders.']'.
                ' Loading packages from <info>'.$shortname.'</> provider'
            );

            if ($this->initialized) {
                $this->http->useMirrors();
            }

            $this->providerPackages = $this->package->getProvider($uri);
            $generator = $this->package->getGenerator($this->providerPackages);
            $this->progressBar->start(count($this->providerPackages));
            $this->poolPackages($generator);
            $this->progressBar->end();
            $this->showErrors()->disableDueErrors()->fallback();
        }

        return $this;
    }

    /**
     * @param Generator $generator
     *
     * @return Create
     */
    protected function poolPackages(Generator $generator):Create
    {
        $this->http->pool(
            $generator,
            // Success
            function ($body, $path) {
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
        return function () {
            $this->progressBar->progress();
        };
    }

    /**
     * Fallback to main mirror when other mirrors failed.
     *
     * @return Create
     */
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
        $generator = $this->package->getGenerator($this->providerPackages);
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
        $countryName = getenv('APP_COUNTRY_NAME');
        $countryCode = getenv('APP_COUNTRY_CODE');
        $maintainerMirror = getenv('MAINTAINER_MIRROR');
        $maintainerProfile = getenv('MAINTAINER_PROFILE');
        $maintainerRepo = getenv('MAINTAINER_REPO');
        $maintainerLicense = getenv('MAINTAINER_LICENSE');
        $tz = getenv('TZ');
        $synced = getenv('SLEEP');
        $googleAnalyticsId = getenv('GOOGLE_ANALYTICS_ID');
        $file = $this->filesystem->getGzName('packages.json');
        $exists = $this->filesystem->hasFile($file);
        $html = $this->filesystem->getFullPath('index.html');

        $lastModified = false;
        if ($exists) {
            $lastModified = filemtime($html);
            unlink($html);
        }

        include_once getcwd().'/resources/index.html.php';
        file_put_contents($html, ob_get_clean());
        return $this;
    }
}
