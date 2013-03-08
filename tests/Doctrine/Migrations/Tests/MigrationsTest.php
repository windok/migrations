<?php

namespace Doctrine\Migrations\Tests;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Migrations;

class MigrationsTest extends \PHPUnit_Framework_TestCase
{
    private $notifier;
    private $migrations;

    protected function setUp()
    {
        $this->notifier = $this->getMockBuilder('Doctrine\Migrations\Notifier')
            ->disableOriginalConstructor()
            ->getMock();
        $this->migrations = new Migrations();
    }

    public function testArrayAccess()
    {
        $this->assertFalse(isset($this->migrations['123']));
        $this->migrations[] = new TestMigration123($this->notifier);
        $this->assertTrue(isset($this->migrations['123']));
        unset($this->migrations['123']);
        $this->assertFalse(isset($this->migrations['123']));
    }

    public function testCountable()
    {
        $this->assertEquals(0, count($this->migrations));
        $this->migrations[] = new TestMigration123($this->notifier);
        $this->assertEquals(1, count($this->migrations));
    }

    public function testSorting()
    {
        $this->migrations[] = new TestMigration124($this->notifier);
        $this->migrations[] = new TestMigration123($this->notifier);

        $migrations = array_values($this->migrations->getMigrations());
        $this->assertEquals($this->migrations['123'], $migrations[0]);
        $this->assertEquals($this->migrations['124'], $migrations[1]);
    }

    /**
     * @expectedException \Doctrine\Migrations\MigrationException
     */
    public function testDuplicateMigrationException()
    {
        $this->migrations[] = new TestMigration123($this->notifier);
        $this->migrations[] = new TestMigration123($this->notifier);
    }

    public function testGetNewest()
    {
        $this->migrations[] = new TestMigration124($this->notifier);
        $this->migrations[] = new TestMigration123($this->notifier);

        $this->assertSame($this->migrations['124'], $this->migrations->getNewest());
    }
}

class TestMigration123 extends AbstractMigration
{
    public function getVersion()
    {
        return 123;
    }

    public function up()
    {
    }

    public function down()
    {
    }
}

class TestMigration124 extends AbstractMigration
{
    public function getVersion()
    {
        return 124;
    }

    public function up()
    {
    }

    public function down()
    {
    }
}
