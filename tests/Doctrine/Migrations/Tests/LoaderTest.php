<?php

namespace Doctrine\Migrations\Tests;

use Doctrine\Migrations\Factory;
use Doctrine\Migrations\Loader;

class LoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testLoad()
    {
        $notifier = $this->getMockBuilder('Doctrine\Migrations\Notifier')
            ->disableOriginalConstructor()
            ->getMock();

        $factory = new Factory(function(\ReflectionClass $class) use ($notifier) {
            return $class->newInstance($notifier);
        });
        $loader = new Loader($factory);
        $migrations = $loader->load(array(__DIR__.'/../../../Fixtures/Agnostic'));
        $this->assertEquals(2, count($migrations));
    }

    public function testFindClass()
    {
        $this->assertEquals('Fixtures\Agnostic\Version123', Loader::findClass(__DIR__.'/../../../Fixtures/Agnostic/Version123.php'));
    }
}
