<?php

declare(strict_types=1);

/*
 * This file is part of the Packagist Mirror.
 *
 * For the full license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Webs\Mirror;

use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use stdClass;
use Exception;
use Generator;
use Closure;

/**
 * Middleware to http operations.
 *
 * @author Webysther Nunes <webysther@gmail.com>
 */
class Http
{
    use Gzip;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Mirror
     */
    protected $mirror;

    /**
     * @var array
     */
    protected $poolErrors;

    /**
     * @var array
     */
    protected $poolErrorsCount = [];

    /**
     * @var array
     */
    protected $config = [
        'base_uri' => '',
        'headers' => ['Accept-Encoding' => 'gzip'],
        'decode_content' => false,
        'timeout' => 30,
        'connect_timeout' => 15,
    ];

    /**
     * @var int
     */
    protected $maxConnections;

    /**
     * @var int
     */
    protected $connections;

    /**
     * @var bool
     */
    protected $usingMirrors = false;

    /**
     * @param Mirror $mirror
     * @param int    $maxConnections
     */
    public function __construct(Mirror $mirror, int $maxConnections)
    {
        if (getenv('DISABLE_GZIP') == 'TRUE'){
            unset(($this->config)['headers']);
        }
        $this->config['base_uri'] = $mirror->getMaster();
        $this->client = new Client($this->config);
        $this->maxConnections = $maxConnections;
        $this->mirror = $mirror;
    }

    /**
     * @return string
     */
    public function getBaseUri():string
    {
        return $this->config['base_uri'];
    }

    /**
     * Client get with transparent gz decode.
     *
     * @see Client::get
     */
    public function getJson(string $uri):stdClass
    {
        $response = $this->client->get($uri);

        // Maybe 4xx or 5xx
        if ($response->getStatusCode() >= 400) {
            throw new Exception("Error download $uri", 1);
        }

        $json = $this->decode((string) $response->getBody());
        $decoded = json_decode($json);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Response not a json: $json", 1);
        }

        return $decoded;
    }

    /**
     * Create a new get request.
     *
     * @param string $uri
     *
     * @return Request
     */
    public function getRequest(string $uri):Request
    {
        $base = $this->getBaseUri();
        if ($this->usingMirrors) {
            $base = $this->mirror->getNext();
        }

        return new Request('GET', $base.'/'.$uri);
    }

    /**
     * @param Generator $requests
     * @param Closure   $success
     * @param Closure   $complete
     *
     * @return Http
     */
    public function pool(Generator $requests, Closure $success, Closure $complete):Http
    {
        $this->connections = $this->maxConnections;
        if ($this->usingMirrors) {
            $mirrors = $this->mirror->getAll()->count();
            $this->connections = $this->maxConnections * $mirrors;
        }

        $fulfilled = function ($response, $path) use ($success, $complete) {
            $body = (string) $response->getBody();
            $success($body, $path);
            $complete();
        };

        $rejected = function ($reason, $path) use ($complete) {
            $uri = $reason->getRequest()->getUri();
            $host = $uri->getScheme().'://'.$uri->getHost();

            $wordwrap = wordwrap($reason->getMessage());
            $message = current(explode("\n", $wordwrap)).'...';

            $this->poolErrors[$path] = [
                'code' => $reason->getCode(),
                'host' => $host,
                'message' => $message,
            ];

            if (!isset($this->poolErrorsCount[$host])) {
                $this->poolErrorsCount[$host] = 0;
            }
            ++$this->poolErrorsCount[$host];
            $complete();
        };

        $this->poolErrors = [];
        $pool = new Pool(
            $this->client,
            $requests,
            [
                'concurrency' => $this->connections,
                'fulfilled' => $fulfilled,
                'rejected' => $rejected,
            ]
        );
        $pool->promise()->wait();

        // Reset to use only max connections for one mirror
        $this->usingMirrors = false;

        return $this;
    }

    /**
     * @return Http
     */
    public function useMirrors():Http
    {
        $this->usingMirrors = true;

        return $this;
    }

    /**
     * @return array
     */
    public function getPoolErrors():array
    {
        return $this->poolErrors;
    }

    /**
     * @param string $mirror
     *
     * @return int
     */
    public function getTotalErrorByMirror(string $mirror):int
    {
        if (!isset($this->poolErrorsCount[$mirror])) {
            return 0;
        }

        return $this->poolErrorsCount[$mirror];
    }

    /**
     * @return Mirror
     */
    public function getMirror():Mirror
    {
        return $this->mirror;
    }
}
