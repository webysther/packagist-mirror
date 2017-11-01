<?php

declare(strict_types=1);

/*
 * This file is part of the Packagist Mirror.
 *
 * For the full license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Webs\Mirror;

use PHPSnippets\DataStructures\CircularArray;

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
     * @var CircularArray
     */
    protected $all;

    /**
     * @var array
     */
    protected $data;

    /**
     * @param string $master
     * @param array  $slaves
     */
    public function __construct(string $master, array $slaves)
    {
        $this->master = $master;
        $this->slaves = $slaves;

        $this->data = $slaves;
        if(!in_array($master, $this->data)){
            $this->data[] = $master;
        }

        $this->all = CircularArray::fromArray($this->data);
    }

    /**
     * @return string
     */
    public function getMaster():string
    {
        return $this->master;
    }

    /**
     * Get all mirrors.
     *
     * @return CircularArray
     */
    public function getAll():CircularArray
    {
        return $this->all;
    }

    /**
     * Get next item.
     *
     * @return string
     */
    public function getNext():string
    {
        $this->all->next();

        return $this->all->current();
    }

    /**
     * @param string $value
     *
     * @return CircularArray
     */
    public function remove(string $value):CircularArray
    {
        $this->data = array_values(array_diff($this->data, [$value]));
        $this->all = CircularArray::fromArray($this->data);

        return $this->getAll();
    }

    /**
     * @return array
     */
    public function toArray():array
    {
        return $this->getAll()->toArray();
    }
}
