<?php

declare(strict_types=1);

/*
 * This file is part of the Packagist Mirror.
 *
 * For the full license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use Webs\Mirror\Command\Base;

namespace Webs\Mirror;

/**
 * Middleware to package operations.
 *
 * @author Webysther Nunes <webysther@gmail.com>
 */
class Package
{
    use Console;

    /**
     * @var array
     */
    protected $packagesDownloaded = [];

    /**
     * @var Http
     */
    protected $http;

    /**
     * @var stdClass
     */
    protected $mainJson;

    /**
     * Main files.
     */
    const MAIN = Base::MAIN;

    /**
     * Add a http.
     *
     * @param Http $http
     *
     * @return Base
     */
    public function setHttp(Http $http):Base
    {
        $this->http = $http;

        return $this;
    }

    /**
     * @param string $path
     */
    public function setDownloaded(string $path):Package
    {
        $this->packagesDownloaded[] = $path;

        return $this;
    }

    /**
     * @return array
     */
    public function getDownloaded():array
    {
        return $this->packagesDownloaded;
    }

    /**
     * @return stdClass
     */
    public function loadMainJson():stdClass
    {
        if (isset($this->mainJson)) {
            return $this->mainJson;
        }

        $this->mainJson = $this->http->getJson(self::MAIN);

        return $this->mainJson;
    }

    /**
     * @param stdClass $providers
     *
     * @return array
     */
    public function normalize(stdClass $providers):array
    {
        $providerPackages = [];
        foreach ($providers as $name => $hash) {
            $uri = sprintf('p/%s$%s.json', $name, $hash->sha256);
            $providerPackages[$uri] = $hash->sha256;
        }

        return $providerPackages;
    }
}
