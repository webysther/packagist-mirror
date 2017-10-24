<?php

namespace Webs\Mirror\Tests;

use PHPUnit_Framework_TestCase;
use Webs\Mirror\Util;

class UtilTest extends PHPUnit_Framework_TestCase
{
    public function testNothing()
    {
        $util = new Util();
        $this->assertSame(Util::class, get_class($util));
    }
}
