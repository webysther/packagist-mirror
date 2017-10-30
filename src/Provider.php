<?php

declare(strict_types=1);

/*
 * This file is part of the Packagist Mirror.
 *
 * For the full license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Webs\Mirror;

use stdClass;
use Exception;

/**
 * Middleware to provider operations.
 *
 * @author Webysther Nunes <webysther@gmail.com>
 */
class Provider
{
    use Console;

    /**
     * @var Http
     */
    protected $http;

    /**
     * Add a http.
     *
     * @param Http $http
     *
     * @return Base
     */
    public function setHttp(Http $http):Provider
    {
        $this->http = $http;

        return $this;
    }

    /**
     * Add base url of packagist.org to services on packages.json of
     * mirror don't support.
     *
     * @param stdClass $providers List of providers from packages.json
     */
    public function addFullPath(stdClass $providers):stdClass
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
     * Load provider includes.
     *
     * @param stdClass $providers
     *
     * @return array
     */
    public function normalize(stdClass $providers):array
    {
        if (!property_exists($providers, 'provider-includes')) {
            throw new Exception('Not found providers information');
        }

        $providerIncludes = $providers->{'provider-includes'};

        $includes = [];
        foreach ($providerIncludes as $name => $hash) {
            $uri = str_replace('%hash%', $hash->sha256, $name);
            $includes[$uri] = $hash->sha256;
        }

        return $includes;
    }
}
