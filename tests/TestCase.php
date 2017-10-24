<?php

namespace Webs\Mirror\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Dotenv\Dotenv;

abstract class TestCase extends BaseTestCase
{
    /**
     * Constructs a test case with the given name.
     *
     * @param string $name
     * @param array  $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        chdir(__DIR__.'/..');

        $dotenv = new Dotenv(getcwd());
        $dotenv->load();
    }
}
