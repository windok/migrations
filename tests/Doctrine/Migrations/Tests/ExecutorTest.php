<?php

namespace Doctrine\Migrations\Tests;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Executor;
use Doctrine\Migrations\Migrations;
use Fixtures\Agnostic\Version123;
use Fixtures\Agnostic\Version124;

class ExecutorTest extends \PHPUnit_Framework_TestCase
{
    private $notifier;
    private $logger;
    private $outputWriter;
    private $executor;

    protected function setUp()
    {
        $this->notifier = $this->getMockBuilder('Doctrine\Migrations\Notifier')
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockBuilder('Doctrine\Migrations\Logger')
            ->disableOriginalConstructor()
            ->getMock();
        $this->outputWriter = $this->getMockBuilder('Doctrine\Migrations\OutputWriter')
            ->disableOriginalConstructor()
            ->getMock();
        $this->executor = new Executor($this->logger, $this->outputWriter);
    }

    public function testGetMigrationsToExecute()
    {
        $migrations = new Migrations();
        $migrations[] = new Version123($this->notifier);
        $migrations[] = new Version124($this->notifier);

        $migrationsToExecute = $this->executor->getMigrationsToExecute($migrations, 123);
        $this->assertEquals(1, count($migrationsToExecute[0]));
        $this->assertEquals(0, count($migrationsToExecute[1]));

        $migrationsToExecute = $this->executor->getMigrationsToExecute($migrations, 124);
        $this->assertEquals(2, count($migrationsToExecute[0]));
        $this->assertEquals(0, count($migrationsToExecute[1]));

        $this->logger->expects($this->once())
            ->method('getUpLogs')
            ->will($this->returnValue(array(
                123 => array()
            )));

        $migrationsToExecute = $this->executor->getMigrationsToExecute($migrations, 124);
        $this->assertEquals(1, count($migrationsToExecute[0]));
        $this->assertEquals(0, count($migrationsToExecute[1]));
        $this->assertSame($migrations[124], $migrationsToExecute[0][124]);
    }

    /**
     * @dataProvider getTestExecuteData
     */
    public function testExecute($migration, $direction)
    {
        if ($direction === 'up') {
            $migration->expects($this->once())
                ->method('preUp');

            $migration->expects($this->once())
                ->method('postUp');
        } else {
            $migration->expects($this->once())
                ->method('preDown');

            $migration->expects($this->once())
                ->method('postDown');
        }

        $migration->expects($this->at(0))
            ->method('setState')
            ->with(AbstractMigration::STATE_PRE);

        $migration->expects($this->at(2))
            ->method('setState')
            ->with(AbstractMigration::STATE_EXEC);

        $migration->expects($this->at(4))
            ->method('setState')
            ->with(AbstractMigration::STATE_POST);

        $migration->expects($this->at(5))
            ->method('setState')
            ->with(AbstractMigration::STATE_NONE);

        $this->executor->execute($migration, $direction);
    }

    public function getTestExecuteData()
    {
        return array(
            array($this->buildMockMigration(), 'up'),
            array($this->buildMockMigration(), 'down')
        );
    }

    protected function buildMockMigration()
    {
        $migration = $this->getMockBuilder('Doctrine\Migrations\AbstractMigration')
            ->disableOriginalConstructor()
            ->getMock();

        return $migration;
    }
}
