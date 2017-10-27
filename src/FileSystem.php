<?php

declare(strict_types=1);

/*
 * This file is part of the Packagist Mirror.
 *
 * For the full license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use League\Flysystem\Filesystem as FlyFilesystem;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Cached\CachedAdapter;
use League\Flysystem\Cached\Storage\Memory;
use FilesystemIterator;
use Exception;

namespace Webs\Mirror;

/**
 * Middleware to access filesystem with transparent gz encode/decode.
 *
 * @author Webysther Nunes <webysther@gmail.com>
 */
class Filesystem
{
    use GZip;

    /**
     * @var FlysystemFilesystem
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $directory;

    /**
     * Ephemeral cache for folder files count
     *
     * @var array
     */
    protected $countedFolder = [];

    /**
     * @param string  $dir        Base directory
     * @param boolean $initialize If true initialize the filesystem access
     */
    public function __construct($baseDirectory, $initialize = true)
    {
        $this->directory = realpath($baseDirectory);

        if($initialize){
            $this->initialize();
        }
    }

    /**
     * Initialize the filesystem access
     *
     * @return Filesystem
     */
    public function initialize():Filesystem
    {
        // Create the adapter
        $localAdapter = new Local($this->directory);

        // Create the cache store
        $cacheStore = new Memory();

        // Decorate the adapter
        $adapter = new CachedAdapter($localAdapter, $cacheStore);

        // And use that to create the file system
        $this->filesystem = new FlyFilesystem($adapter);
        return $this;
    }

    /**
     * Normalize path to use .gz
     *
     * @param  string $path
     * @return string
     */
    public function normalize(string $path):string
    {
        if(substr($path, -3) !== '.gz'){
            return $path.'.gz';
        }

        return $path;
    }

    /**
     * Decode from gz after read from disk
     *
     * @see FlyFilesystem::read
     */
    public function read(string $path):string
    {
        return $this->decode($this->filesystem->read($path));
    }

    /**
     * Encode to gz before write to disk with hash checking
     *
     * @see FlyFilesystem::write
     */
    public function write(string $path, string $contents):Filesystem
    {
        $path = $this->normalize($path);
        $this->filesystem->write($path, $this->encode($contents));

        if($this->hash($contents) != $this->hashFile($path)){
            throw new Exception("Write file $path hash failed", 1);
        }

        return $this;
    }

    /**
     * Simple touch
     *
     * @param  string $path
     * @return Filesystem
     */
    public function touch(string $path):Filesystem
    {
        $this->filesystem->write($path, '');
        return $this;
    }

    /**
     * Create a symlink
     *
     * @param  string $file
     * @param  string $link
     * @return Filesystem
     */
    public function symlink(string $file, string $link):Filesystem
    {
        if (!$this->has($file)) {
            throw new Exception("File $file not found", 1);
        }

        if (!$this->has($link)) {
            symlink($this->getFullPath($file), $this->getFullPath($link));
        }

        return $this;
    }

    /**
     * @see FlyFilesystem::has
     */
    public function has(string $path):bool
    {
        return $this->filesystem->has($path);
    }

    /**
     * Rename less strict
     *
     * @param  string       $from
     * @param  string       $to
     * @param  bool|boolean $strict If true check $from exists e throw
     *                              Exception if not found
     * @return Filesystem
     */
    public function move(string $from, string $to, bool $strict = false):Filesystem
    {
        if(!$this->has($from)){
            $strict && throw new Exception("File $from not found", 1);
        }

        $this->filesystem->rename($from, $to);
        return $this;
    }

    /**
     * @see FlyFilesystem::delete
     * @see FlyFilesystem::deleteDir
     */
    public function delete(string $fileOrDirectory):Filesystem
    {
        $path = $this->getFullPath($fileOrDirectory);

        if(is_dir($path)){
            $this->filesystem->deleteDir($fileOrDirectory);
            return;
        }

        if (is_link($path)) {
            unlink($path);
            return;
        }

        $this->filesystem->delete($fileOrDirectory);
        return $this;
    }

    /**
     * Glob without file sort
     *
     * @param  string $pattern
     * @return array
     */
    public function glob(string $pattern):array
    {
        $return = glob($pattern, GLOB_NOSORT);

        if($return === false){
            return [];
        }

        return $return;
    }

    /**
     * Count files inside folder, if is a file, return 0
     *
     * @param  string $folder
     * @return int
     */
    public function count(string $folder):int
    {
        $path = $this->getFullPath($folder);
        $hash = $this->hash($path);

        if(!is_dir($path)){
            return 0;
        }

        if (array_key_exists($hash, $this->countedFolder)) {
            return $this->countedFolder[$hash];
        }

        $iterator = new FilesystemIterator(
            $path,
            FilesystemIterator::SKIP_DOTS
        );

        $totalFiles = iterator_count($iterator);
        $this->countedFolder[$hash] = $totalFiles;
        return $totalFiles;
    }

    /**
     * Get full path
     *
     * @param  string $path
     * @return string
     */
    public function getFullPath(string $path):string
    {
        return $this->directory.DIRECTORY_SEPARATOR.$path;
    }

    /**
     * Calculates SHA256
     *
     * @param string $string
     * @return string
     */
    public function hash(string $string):string
    {
        return hash('sha256', $string);
    }

    /**
     * Calculates SHA256 for file
     *
     * @param  string $path
     * @return string
     */
    public function hashFile(string $path):string
    {
        // dont use hash_file because content is saved with gz
        return $this->hash($this->filesystem->read($path));
    }

    /**
     * Check diff between two string using SHA256
     *
     * @param  string $from
     * @param  string $to
     * @return boolean
     */
    public function isEqual(string $from, string $to):bool
    {
        return $this->hash($from) === $this->hash($to);
    }

    /**
     * Check diff between two files using SHA256
     *
     * @param  string $from
     * @param  string $to
     * @return boolean
     */
    public function isEqualFile(string $from, string $to):bool
    {
        return $this->hashFile($from) === $this->hashFile($to);
    }
}
