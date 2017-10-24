<?php

namespace Webs\Mirror\Tests;

use Webs\Mirror\Util;

class UtilTest extends TestCase
{
    public function testNothing()
    {
        $util = new Util();
        $this->assertSame(Util::class, get_class($util));
    }
}
