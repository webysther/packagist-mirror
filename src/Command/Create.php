<?php

declare(strict_types=1);

/*
 * This file is part of the Packagist Mirror.
 *
 * For the full license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace League\Mirror\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Pool;
use stdClass;
use Generator;
use Dariuszp\CliProgressBar;
use League\Mirror\Util;

/**
 * Create a mirror.
 *
 * @author Webysther Nunes <webysther@gmail.com>
 */
class Create extends Command
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
    public function execute(InputInterface $input, OutputInterface $output):int
    {
        $util = new Util();

        $this->input = $input;
        $this->output = $output;
        $this->client = new Client([
            'base_uri' => 'https://'.getenv('MAIN_MIRROR').'/',
            'headers' => ['Accept-Encoding' => 'gzip'],
        ]);

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

        $cachedir = getenv('PUBLIC_DIR').'/';
        if (file_exists($cachedir.'.init')) {
            unlink($cachedir.'.init');
        }

        $util->showResults($input, $output);

        return 0;
    }

    /**
     * Switch current packagist.json to space and .packagist to packagist.json.
     *
     * @return bool True if work, false otherside
     */
    protected function switch()
    {
        $cachedir = getenv('PUBLIC_DIR').'/';
        $packages = $cachedir.'packages.json';
        $dotPackages = $cachedir.'.packages.json';

        if (file_exists($dotPackages)) {
            if (file_exists($packages)) {
                $this->output->writeln('Removing old packages.json');
                unlink($packages);
            }

            $this->output->writeln('Switch .packages.json to packages.json');
            copy($dotPackages, $packages);
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
        $cachedir = getenv('PUBLIC_DIR').'/';
        $packages = $cachedir.'packages.json';

        if (!file_exists($cachedir)) {
            mkdir($cachedir, 0777, true);
        }

        $this->output->writeln('Loading providers information');

        $response = $this->client->get('packages.json');

        // Maybe 4xx or 5xx
        if ($response->getStatusCode() >= 400) {
            $this->output->writeln('Error download source of providers');

            return false;
        }

        $json = (string) $response->getBody();
        $providers = json_decode($json);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->output->writeln('JSON from main mirror isn\'t valid');

            return false;
        }

        // Add full path for services of mirror don't provide only packagist.org
        foreach (['notify', 'notify-batch', 'search'] as $key) {
            $path = parse_url($providers->$key){'path'};
            $providers->$key = 'https://'.getenv('MAIN_MIRROR').'/'.$path;
        }
        $fail = file_put_contents(
            $cachedir.'.packages.json', // .packages.json
            json_encode($providers, JSON_PRETTY_PRINT)
        );

        // No provider changed? Just relax...
        if (file_exists($packages) && !file_exists($cachedir.'.init')) {
            $newSHA256 = hash(
                'sha256',
                file_get_contents($cachedir.'.packages.json')
            );

            if ($newSHA256 == hash('sha256', file_get_contents($packages))) {
                unlink($cachedir.'.packages.json');
                $this->output->writeln('Up-to-date');

                return true;
            }
        }

        if (false === $fail) {
            $this->output->writeln('Error to create file \'.packages.json\'...');

            return false;
        }

        if (empty($providers->{'provider-includes'})) {
            $this->output->writeln('Not found providers information...');

            return false;
        }

        $includes = count((array) $providers->{'provider-includes'});
        $this->bar = new CliProgressBar($includes, 0);
        $this->bar->display();

        $generator = $this->downloadProvideIncludes(
            $providers->{'provider-includes'}
        );

        if (!$generator->valid()) {
            $this->output->writeln('All providers up-to-date...');

            return true;
        }

        $this->providers = [];
        $pool = new Pool($this->client, $generator, [
            'concurrency' => getenv('MAX_CONNECTIONS'),
            'fulfilled' => function ($response, $name) {
                $json = (string) $response->getBody();
                file_put_contents($name, $json);
                $this->providers[$name] = json_decode($json);
                $this->bar->progress();
            },
            'rejected' => function ($reason, $name) {
                $this->bar->progress();
            },
        ]);

        // Initiate the transfers and create a promise
        $promise = $pool->promise();

        // Force the pool of requests to complete.
        $promise->wait();

        $this->bar->progress(10);
        $this->bar->end();
        $this->output->writeln('');

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
            $cachename = $cachedir.$fileurl;

            // Only if exists
            if (file_exists($cachename) && !file_exists($cachedir.'.init')) {
                $this->bar->progress();
                continue;
            }

            // if 'p/...' folder not found
            if (!file_exists(dirname($cachename))) {
                touch($cachedir.'.init');
                mkdir(dirname($cachename), 0777, true);
            }

            yield $cachename => new Request('GET', $fileurl);
        }
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

            $this->bar = new CliProgressBar($total, 0);
            $this->bar->display();

            $pool = new Pool($this->client, $generator, [
                'concurrency' => getenv('MAX_CONNECTIONS'),
                'fulfilled' => function ($response, $name) {
                    $json = (string) $response->getBody();
                    file_put_contents($name, $json);
                    $this->packages[] = dirname($name);
                    $this->bar->progress();
                },
                'rejected' => function ($reason, $name) {
                    $this->bar->progress();
                },
            ]);

            // Initiate the transfers and create a promise
            $promise = $pool->promise();

            // Force the pool of requests to complete.
            $promise->wait();

            $this->bar->progress($total);
            $this->bar->end();
            $this->output->writeln('');
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
            $cachename = $cachedir.$fileurl;

            // Only if exists
            if (file_exists($cachename)) {
                $this->bar->progress();
                continue;
            }

            // if 'p/...' folder not found
            if (!file_exists(dirname($cachename))) {
                mkdir(dirname($cachename), 0777, true);
            }

            yield $cachename => new Request('GET', $fileurl);
        }
    }

    /**
     * Find hash and replace by *.
     *
     * @param string $name Name of provider or package
     *
     * @return string Shortname
     */
    protected function shortname(string $name):string
    {
        return preg_replace('/\$(\w*)/', '*', $name);
    }
}
