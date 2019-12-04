<?php

namespace Webs\Mirror\Tests;

use Dotenv\Dotenv;
use Dotenv\Repository\Adapter\EnvConstAdapter;
use Dotenv\Repository\RepositoryBuilder;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase as BaseTestCase;

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
        ini_set('memory_limit', '-1');

        parent::__construct($name, $data, $dataName);

        $repository = RepositoryBuilder::create()->withReaders([new EnvConstAdapter()])->immutable()->make();
        Dotenv::create($repository, __DIR__.'/fixture/', null)->load();

        $this->dir = vfsStream::setup()->url();
    }
}
