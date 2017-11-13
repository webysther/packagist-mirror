<?php

declare(strict_types=1);

/*
 * This file is part of the Packagist Mirror.
 *
 * For the full license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Webs\Mirror;

use League\Flysystem\Filesystem as FlyFilesystem;
use League\Flysystem\Adapter\Local;
use org\bovigo\vfs\vfsStream;
use Exception;

/**
 * Middleware to access filesystem with transparent gz encode/decode.
 *
 * @author Webysther Nunes <webysther@gmail.com>
 */
class Filesystem
{
    use GZip;
    use IO;

    /**
     * @var FlyFilesystem
     */
    protected $filesystem;

    /**
     * Ephemeral cache for folder files count.
     *
     * @var array
     */
    protected $countedFolder = [];

    /**
     * @param string $dir        Base directory
     * @param bool   $initialize If true initialize the filesystem access
     */
    public function __construct($baseDirectory)
    {
        $this->directory = $baseDirectory.'/';

        // Create the adapter
        $localAdapter = new Local($this->directory);

        // And use that to create the file system
        $this->filesystem = new FlyFilesystem($localAdapter);
    }

    /**
     * @param FlyFilesystem $filesystem
     */
    public function setFilesystem(FlyFilesystem $filesystem):Filesystem
    {
        $this->filesystem = $filesystem;

        return $this;
    }

    /**
     * Add suffix gz to json file.
     *
     * @param string $path
     *
     * @return string
     */
    public function getGzName(string $path):string
    {
        $fullPath = $this->getFullPath($path);
        $extension = pathinfo($fullPath, PATHINFO_EXTENSION);

        if ($extension == 'json') {
            return $path.'.gz';
        }

        return $path;
    }

    /**
     * Get link name from gz.
     *
     * @param string $path
     *
     * @return string
     */
    protected function getLink(string $path):string
    {
        $fullPath = $this->getFullPath($path);
        $extension = pathinfo($fullPath, PATHINFO_EXTENSION);

        if ($extension == 'gz') {
            return substr($path, 0, -3);
        }

        return $path;
    }

    /**
     * Decode from gz after read from disk.
     *
     * @see FlyFilesystem::read
     */
    public function read(string $path):string
    {
        $path = $this->getGzName($path);
        $file = $this->filesystem->read($path);

        if ($file === false) {
            return '';
        }

        return (string) $this->decode($file);
    }

    /**
     * Encode to gz before write to disk with hash checking.
     *
     * @see FlyFilesystem::write
     */
    public function write(string $path, string $contents):Filesystem
    {
        $file = $this->getGzName($path);
        $this->filesystem->put($file, $this->encode($contents));
        $decoded = $this->decode($contents);

        if ($this->getHash($decoded) != $this->getHashFile($file)) {
            $this->filesystem->delete($file);
            throw new Exception("Write file $path hash failed");
        }

        $this->symlink($file);

        return $this;
    }

    /**
     * Simple touch.
     *
     * @param string $path
     *
     * @return Filesystem
     */
    public function touch(string $path):Filesystem
    {
        if ($this->has($path)) {
            return $this;
        }

        touch($this->getFullPath($path));

        return $this;
    }

    /**
     * @param string $file
     *
     * @return bool
     */
    protected function isGzFile(string $file):bool
    {
        if (substr($this->getGzName($file), -3) == '.gz') {
            return true;
        }

        return false;
    }

    /**
     * Create a symlink.
     *
     * @param string $file
     *
     * @return Filesystem
     */
    protected function symlink(string $file):Filesystem
    {
        if (!$this->hasFile($file) || !$this->isGzFile($file)) {
            return $this;
        }

        $path = $this->getGzName($file);
        $link = $this->getFullPath($this->getLink($path));

        if ($this->hasLink($link)) {
            return $this;
        }

        if (strpos($link, vfsStream::SCHEME.'://') !== false){
            return $this;
        }

        symlink(basename($path), $link);

        return $this;
    }

    /**
     * @see FlyFilesystem::has
     */
    public function has(string $path):bool
    {
        return $this->hasFile($path) && $this->hasLink($path);
    }

    /**
     * @see FlyFilesystem::has
     */
    public function hasFile(string $path):bool
    {
        return file_exists($this->getFullPath($this->getGzName($path)));
    }

    /**
     * @see FlyFilesystem::has
     */
    protected function hasLink(string $path):bool
    {
        return is_link($this->getFullPath($this->getLink($path)));
    }

    /**
     * Move to not dot name of file.
     *
     * @param string $from
     *
     * @return Filesystem
     */
    public function move(string $from):Filesystem
    {
        if (!$this->has($from)) {
            return $this;
        }

        $file = $this->getGzName($from);
        $target = substr($file, 1);

        if ($this->has($target)) {
            $this->delete($target);
        }

        retry(8, function () use ($from, $target) {
            $this->filesystem->rename($this->getGzName($from), $target);
        }, 250);

        $this->symlink($target);
        // remove old symlink
        $this->delete($from);

        return $this;
    }

    /**
     * @see FlyFilesystem::delete
     * @see FlyFilesystem::deleteDir
     */
    public function delete(string $fileOrDirectory):Filesystem
    {
        $path = $this->getFullPath($fileOrDirectory);

        if (is_dir($path)) {
            $this->filesystem->deleteDir($fileOrDirectory);

            return $this;
        }

        $file = $this->getGzName($path);
        if (file_exists($file)) {
            unlink($file);
        }

        $link = $this->getLink($path);
        if (is_link($link)) {
            unlink($link);
        }

        return $this;
    }

    /**
     * Calculates SHA256.
     *
     * @param string $string
     *
     * @return string
     */
    public function getHash(string $string):string
    {
        return hash('sha256', $string);
    }

    /**
     * Calculates SHA256 for file.
     *
     * @param string $path
     *
     * @return string
     */
    public function getHashFile(string $path):string
    {
        // dont use hash_file because content is saved with gz
        return $this->getHash($this->read($path));
    }
}
