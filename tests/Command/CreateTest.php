<?php

declare(strict_types=1);

/*
 * This file is part of the Packagist Mirror.
 *
 * For the full license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Webs\Mirror\Tests\Command;

use League\Flysystem\Config;
use League\Flysystem\Filesystem as FlyFilesystem;
use stdClass;
use Symfony\Component\Console\Input\ArrayInput;
use League\Flysystem\Adapter\Local;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use League\Flysystem\Memory\MemoryAdapter;
use Webs\Mirror\Command\Clean;
use Webs\Mirror\Command\Create;
use Webs\Mirror\Filesystem;
use Webs\Mirror\Http;
use Webs\Mirror\Mirror;
use Webs\Mirror\Package;
use Webs\Mirror\ProgressBar;
use Webs\Mirror\Provider;
use Webs\Mirror\Tests\TestCase;

class CreateTest extends TestCase
{
    public function testBuild()
    {
        $fly = new FlyFilesystem(new MemoryAdapter(), new Config([
            'disable_asserts' => true,
        ]));

        $filesystem = new Filesystem($this->dir);
        $filesystem->setFilesystem($fly);
        $provider = new Provider;
        $package = new Package;

        $progressBar = new ProgressBar;

        $mirror = new Mirror(
            getenv('MAIN_MIRROR'),
            explode(',', getenv('DATA_MIRROR'))
        );
        $http = new Http($mirror, (int) getenv('MAX_CONNECTIONS'));

        $clean = new Clean();
        $clean->setProgressBar($progressBar);
        $clean->setProvider($provider);
        $clean->setPackage($package);
        $clean->setFilesystem($filesystem);
        $clean->setHttp($http);

        $create = new Create();
        $create->setProgressBar($progressBar);
        $create->setProvider($provider);
        $create->setPackage($package);
        $create->setFilesystem($filesystem);
        $create->setClean($clean);
        $create->setHttp($http);

        $this->assertSame(Create::class, get_class($create));

        $definition = new InputDefinition(array(
            new InputOption('no-progress', null, InputOption::VALUE_NONE),
            new InputOption('no-ansi', null, InputOption::VALUE_NONE),
        ));

        $input = new ArrayInput(array());
        $input->bind($definition);

        $output = new NullOutput();

        $create->init($input, $output)->bootstrap();

        $package->setMainJson(
            $this->getPackagesJson($package->getMainJson())
        );

        $this->assertEquals(0, $create->execute($input, $output));

        $definition = new InputDefinition(array(
            new InputOption('scrub', null, InputOption::VALUE_NONE),
            new InputOption('no-ansi', null, InputOption::VALUE_NONE),
        ));

        $input = new ArrayInput(array());
        $input->bind($definition);
        $input->setOption('scrub', true);

        $this->assertEquals(0, $clean->execute($input, $output));
    }

    protected function getPackagesJson(stdClass $json, string $name = 'archived'):stdClass
    {
        $newJson = clone $json;
        $newJson->{'provider-includes'} = new stdClass();

        foreach ($json->{'provider-includes'} as $item => $value) {
            if (strpos((string) $item, $name.'$') === false){
                continue;
            }

            $newJson->{'provider-includes'}->$item = $value;
            break;
        }

        return $newJson;
    }
}
