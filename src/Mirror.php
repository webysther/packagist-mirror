<?php

declare(strict_types=1);

/*
 * This file is part of the Packagist Mirror.
 *
 * For the full license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use PHPSnippets\DataStructures\CircularArray;

namespace Webs\Mirror;

/**
 * Middleware to http operations.
 *
 * @author Webysther Nunes <webysther@gmail.com>
 */
class Mirror
{
    /**
     * @var string
     */
    protected $master;

    /**
     * @var array
     */
    protected $slaves;

    /**
     * @var array
     */
    protected $all;

    /**
     * @param string $master
     * @param array  $slaves
     */
    public function __construct(string $master, array $slaves)
    {
        $this->master = $master;
        $this->slaves = $slaves;
        $this->all = CircularArray::fromArray(
            array_unique(array_merge([$master], $slaves))
        );
    }

    /**
     * @return string
     */
    public function getMaster():string
    {
        return $this->master;
    }

    /**
     * Get all mirrors
     *
     * @return CircularArray
     */
    public function getAll():CircularArray
    {
        return $this->all;
    }

    /**
     * Get next item
     *
     * @return string
     */
    public function getNext():string
    {
        $this->all->next();
        return $this->all->current();
    }
}
