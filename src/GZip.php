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
 * Trait to gzip operations.
 *
 * @author Webysther Nunes <webysther@gmail.com>
 */
trait GZip
{
    /**
     * Check if is gzip, if not compress.
     *
     * @param string $gzip
     *
     * @return string
     */
    public function encode(string $gzip):string
    {
        if ($this->isGzip($gzip)) {
            return $gzip;
        }

        return gzencode($gzip);
    }

    /**
     * Check if is gzip, if yes uncompress.
     *
     * @param string $gzip
     *
     * @return string
     */
    public function decode(string $gzip):string
    {
        if ($this->isGzip($gzip)) {
            return gzdecode($gzip);
        }

        return $gzip;
    }

    /**
     * Check if is gzip
     *
     * @param  string  $gzip
     * @return boolean
     */
    public function isGzip(string $gzip):bool
    {
        if (mb_strpos($gzip, "\x1f"."\x8b"."\x08") === 0) {
            return true;
        }

        return false;
    }
}
