<?php

declare(strict_types=1);

/*
 * This file is part of the Packagist Mirror.
 *
 * For the full license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Webs\Mirror;

use FilesystemIterator;

/**
 * Trait to FlyFilesystem wraper operations.
 *
 * @author Webysther Nunes <webysther@gmail.com>
 */
trait IO
{
    /**
     * @var string
     */
    protected $directory;

    /**
     * Glob without file sort.
     *
     * @param string $pattern
     *
     * @return array
     */
    public function glob(string $pattern):array
    {
        $return = glob($this->getFullPath($pattern), GLOB_NOSORT);

        if ($return === false) {
            return [];
        }

        return $return;
    }

    /**
     * Count files inside folder, if is a file, return 0.
     *
     * @param string $folder
     *
     * @return int
     */
    public function getCount(string $folder):int
    {
        $path = $this->getFullPath($folder);

        if (!is_dir($path)) {
            $path = dirname($path);
        }

        $iterator = new FilesystemIterator(
            $path,
            FilesystemIterator::SKIP_DOTS
        );

        return iterator_count($iterator);
    }

    /**
     * Get full path.
     *
     * @param string $path
     *
     * @return string
     */
    public function getFullPath(string $path):string
    {
        if (strpos($path, $this->directory) !== false) {
            return $path;
        }

        return $this->directory.$path;
    }
}
