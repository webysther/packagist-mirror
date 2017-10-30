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
 * Trait to shortname of package.
 *
 * @author Webysther Nunes <webysther@gmail.com>
 */
trait ShortName
{
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
