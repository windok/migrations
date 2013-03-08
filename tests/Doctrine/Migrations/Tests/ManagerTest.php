<?php

namespace Doctrine\Migrations\Tests;

use Doctrine\Migrations\Manager;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    private $logger;
    private $migrations;
    private $executor;
    private $migration1;
    private $migration2;
    private $manager;

    protected function setUp()
    {
        $this->logger = $this->getMockBuilder('Doctrine\Migrations\Logger')
            ->disableOriginalConstructor()
            ->getMock();
        $this->migrations = $this->getMockBuilder('Doctrine\Migrations\Migrations')
            ->disableOriginalConstructor()
            ->getMock();
        $this->executor = $this->getMockBuilder('Doctrine\Migrations\Executor')
            ->disableOriginalConstructor()
            ->getMock();
        $this->migration1 = $this->getMockBuilder('Doctrine\Migrations\AbstractMigration')
            ->disableOriginalConstructor()
            ->getMock();
        $this->migration2 = $this->getMockBuilder('Doctrine\Migrations\AbstractMigration')
            ->disableOriginalConstructor()
            ->getMock();
        $this->manager = new Manager($this->logger, $this->migrations, $this->executor);
    }

    public function testGetLogger()
    {
        $this->assertSame($this->logger, $this->manager->getLogger());
    }

    public function testGetMigrations()
    {
        $this->assertSame($this->migrations, $this->manager->getMigrations());
    }

    public function testGetExecutor()
    {
        $this->assertSame($this->executor, $this->manager->getExecutor());
    }

    public function testExecute()
    {
        $this->migrations->expects($this->once())
            ->method('offsetGet')
            ->with(123)
            ->will($this->returnValue($this->migration1));

        $this->executor->expects($this->once())
            ->method('execute')
            ->with($this->migration1, 'up');

        $this->manager->execute(123, 'up');
    }

    public function testMigrateNothingToDo()
    {
        $this->migrations->expects($this->once())
            ->method('count')
            ->will($this->returnValue(0));

        $this->migrations->expects($this->exactly(0))
            ->method('getNewest');

        $this->manager->migrate();
    }

    public function testMigrate()
    {
        $this->migrations->expects($this->once())
            ->method('count')
            ->will($this->returnValue(1));

        $this->migrations->expects($this->any())
            ->method('getNewest')
            ->will($this->returnValue($this->migration2));

        $this->migration1->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValue(123));

        $this->migration2->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValue(124));

        $this->executor->expects($this->once())
            ->method('getMigrationsToExecute')
            ->with($this->migrations, 124)
            ->will($this->returnValue(array(array($this->migration1), array($this->migration2))));

        $this->executor->expects($this->at(1))
            ->method('execute')
            ->with($this->migration2, 'down');

        $this->executor->expects($this->at(2))
            ->method('execute')
            ->with($this->migration1, 'up');

        $this->manager->migrate();
    }

    public function testHasVersion()
    {
        $this->migrations->expects($this->once())
            ->method('offsetExists')
            ->with(123)
            ->will($this->returnValue(true));

        $this->assertTrue($this->manager->hasVersion(123));
    }

    public function testIsVersionMigrated()
    {
        $this->migrations->expects($this->once())
            ->method('offsetExists')
            ->with(123)
            ->will($this->returnValue(true));

        $this->logger->expects($this->once())
            ->method('getUpLogs')
            ->will($this->returnValue(array(123 => array())));

        $this->assertTrue($this->manager->isVersionMigrated(123));
    }

    public function testGetCurrentVersion()
    {
        $this->logger->expects($this->once())
            ->method('getCurrentVersion')
            ->will($this->returnValue(123));

        $this->assertEquals(123, $this->manager->getCurrentVersion());
    }

    public function testGetNewestVersion()
    {
        $this->migrations->expects($this->any())
            ->method('getNewest')
            ->will($this->returnValue($this->migration1));

        $this->migration1->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValue(123));

        $this->assertEquals(123, $this->manager->getNewestVersion());
    }

    public function testGetExecutedMigrations()
    {
        $this->logger->expects($this->once())
            ->method('getUpLogs')
            ->will($this->returnValue(array(123 => array(), 124 => array())));

        $this->migrations->expects($this->at(0))
            ->method('offsetGet')
            ->with(123)
            ->will($this->returnValue($this->migration1));

        $this->migrations->expects($this->at(1))
            ->method('offsetGet')
            ->with(124)
            ->will($this->returnValue($this->migration2));

        $this->assertEquals(array(123 => $this->migration1, 124 => $this->migration2), $this->manager->getExecutedMigrations());
    }
}
