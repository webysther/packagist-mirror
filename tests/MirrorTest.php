<?php

namespace Webs\Mirror\Tests;

use Webs\Mirror\Mirror;

class MirrorTest extends TestCase
{
    public function testDuplicatedEntry()
    {
        $master = 'https://localhost';

        // duplicate entry
        $slaves = [$master, 'http://0.0.0.0'];

        $mirror = new Mirror($master, $slaves);
        $this->assertSame($slaves, $mirror->toArray());
    }
}
