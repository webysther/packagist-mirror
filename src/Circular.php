<?php

declare(strict_types=1);

/*
 * This file is part of the Packagist Mirror.
 *
 * For the full license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Webs\Mirror;

use SplFixedArray;

/**
 * Circular array
 *
 * @author Webysther Nunes <webysther@gmail.com>
 */
class Circular extends SplFixedArray
{
    public function next()
    {
        if($this->key()+1 == $this->count()){
            $this->rewind();
            return;
        }

        parent::next();
    }

    public static function fromArray($array, $save_indexes = true)
    {
        $circular = new Circular(count($array));

        foreach ($array as $key => $value) {
            $circular[$key] = $value;
        }

        return $circular;
    }
}
