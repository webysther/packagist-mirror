<?php

declare(strict_types=1);

/*
 * This file is part of the Packagist Mirror.
 *
 * For the full license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Webs\Mirror;

/**
 * Middleware to provider operations.
 *
 * @author Webysther Nunes <webysther@gmail.com>
 */
class Provider
{
    use Console;

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

        $providerIncludes = [];
        foreach ($providerIncludes as $name => $hash) {
            $uri = str_replace('%hash%', $hash->sha256, $name);
            $providerIncludes[$uri] = $hash->sha256;
        }

        return $providerIncludes;
    }
}
