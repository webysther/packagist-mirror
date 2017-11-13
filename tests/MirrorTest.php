<?php

declare(strict_types=1);

/*
 * This file is part of the Packagist Mirror.
 *
 * For the full license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Webs\Mirror\Tests;

use Webs\Mirror\Mirror;

class MirrorTest extends TestCase
{
    public function testDuplicatedEntry()
    {
        $master = 'https://localhost';
        $slave = 'http://0.0.0.0';

        // duplicate entry
        $slaves = [$master, $slave];

        $mirror = new Mirror($master, [$slave]);
        $mirror = new Mirror($master, $slaves);
        $this->assertSame($slaves, $mirror->toArray());
        $this->assertSame($master, $mirror->getMaster());
        $this->assertSame($slave, $mirror->getNext());
        $this->assertSame($slave, $mirror->remove($master)->current());
    }
}
