<?php

namespace Doctrine\Migrations\Tests;

use Doctrine\Migrations\AbstractMigration;

class AbstractMigrationTest extends \PHPUnit_Framework_TestCase
{
    public function testState()
    {
        $notifier = $this->getMockBuilder('Doctrine\Migrations\Notifier')
            ->disableOriginalConstructor()
            ->getMock();
        $migration = new TestAbstractMigration($notifier);
        $this->assertEquals(AbstractMigration::STATE_NONE, $migration->getState());
        $migration->setState(AbstractMigration::STATE_PRE);
        $this->assertEquals(AbstractMigration::STATE_PRE, $migration->getState());
        $this->assertEquals(123, $migration->getVersion());
        $this->assertEquals('123', (string) $migration);

        $migration->up();
        $migration->down();
        $migration->preUp();
        $migration->postUp();
        $migration->preDown();
        $migration->postDown();
    }
}

class TestAbstractMigration extends AbstractMigration
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
