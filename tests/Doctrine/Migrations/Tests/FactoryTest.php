<?php

namespace Doctrine\Migrations\Tests;

use Doctrine\Migrations\Factory;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $factory = new Factory(function(\ReflectionClass $class) {
            $instance = $class->newInstance();
            $instance->test = true;
            return $instance;
        });
        $class = new \ReflectionClass('stdClass');
        $object = $factory->newInstance($class);
        $this->assertInstanceOf('stdClass', $object);
        $this->assertTrue($object->test);
    }
}
