<?php

namespace Doctrine\Migrations\Tests;

use Doctrine\Migrations\Executor;
use Doctrine\Migrations\Factory;
use Doctrine\Migrations\Loader;
use Doctrine\Migrations\Logger;
use Doctrine\Migrations\Manager;
use Doctrine\Migrations\Migration\AbstractDBALMigration;
use Doctrine\Migrations\Notifier;
use Doctrine\Migrations\OutputWriter;

class FunctionalSanityTest extends \PHPUnit_Framework_TestCase
{
    public function testDbal()
    {
        $connection = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $outputWriter = new OutputWriter();
        $notifier = new Notifier($outputWriter);

        $factory = new Factory(function(\ReflectionClass $class) use ($notifier, $connection) {
            return $class->newInstance($notifier, $connection);
        });

        $loader = new Loader($factory);
        $migrations = $loader->load(array(__DIR__.'/../../../Fixtures/DBAL'));

        $storage = new ArrayLoggerStorage();
        $logger = new Logger($storage);

        $executor = new Executor($logger, $outputWriter);
        $manager = new Manager($logger, $migrations, $executor);

        $connection->expects($this->once())
            ->method('executeQuery')
            ->with('INSERT INTO users (username) VALUES (?)', array('username'), array());

        $manager->migrate();
    }

    public function testMongoDB()
    {
        $connection = $this->getMockBuilder('Doctrine\MongoDB\Connection')
            ->disableOriginalConstructor()
            ->getMock();
        $database = $this->getMockBuilder('Doctrine\MongoDB\Database')
            ->disableOriginalConstructor()
            ->getMock();
        $collection = $this->getMockBuilder('Doctrine\MongoDB\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $outputWriter = new OutputWriter();
        $notifier = new Notifier($outputWriter);

        $factory = new Factory(function(\ReflectionClass $class) use ($notifier, $connection) {
            return $class->newInstance($notifier, $connection);
        });

        $loader = new Loader($factory);
        $migrations = $loader->load(array(__DIR__.'/../../../Fixtures/MongoDB'));

        $storage = new ArrayLoggerStorage();
        $logger = new Logger($storage);

        $executor = new Executor($logger, $outputWriter);
        $manager = new Manager($logger, $migrations, $executor);

        $connection->expects($this->any())
            ->method('selectDatabase')
            ->with('test')
            ->will($this->returnValue($database));

        $database->expects($this->any())
            ->method('selectCollection')
            ->with('test')
            ->will($this->returnValue($collection));

        $manager->migrate();
    }
}
