<?php

declare(strict_types=1);

/*
 * This file is part of the Packagist Mirror.
 *
 * For the full license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;

namespace Webs\Mirror;

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
        $this->config['base_uri'] = $mirror->getMaster().'/';
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
            $base = $this->mirrors->getNext().'/';
        }

        return new Request('GET', $base.$uri);
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

        $this->poolErrors = [];
        (new Pool(
            $this->client,
            $requests,
            [
                'concurrency' => $this->connections,
                'fulfilled' => function ($response, $path) {
                    $body = (string) $response->getBody();
                    $success($this->decode($body), $path);
                    $complete();
                },
                'rejected' => function ($reason, $path) {
                    $this->poolErrors[$path] = $reason;
                    $complete();
                },
            ]
        ))->promise()->wait();

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
     * @return Mirror
     */
    public function getMirror():Mirror
    {
        return $this->mirror;
    }
}
