<?php

namespace Doctrine\Migrations\Tests;

use Doctrine\Migrations\AbortMigrationException;
use Doctrine\Migrations\IrreversibleMigrationException;
use Doctrine\Migrations\SkipMigrationException;
use Doctrine\Migrations\Notifier;

class NotifierTest extends \PHPUnit_Framework_TestCase
{
    private $outputWriter;
    private $notifier;

    protected function setUp()
    {
        $this->outputWriter = $this->getMockBuilder('Doctrine\Migrations\OutputWriter')
            ->disableOriginalConstructor()
            ->getMock();
        $this->notifier = new Notifier($this->outputWriter);
    }

    public function testWrite()
    {
        $this->outputWriter->expects($this->once())
            ->method('write')
            ->with('test');

        $this->notifier->write('test');
    }

    public function testThrowIrreversibleMigrationException()
    {
        try {
            $this->notifier->throwIrreversibleMigrationException('test');
            $this->fail();
        } catch (IrreversibleMigrationException $e) {
            $this->assertEquals('test', $e->getMessage());
        }
    }

    public function testThrowIrreversibleMigrationExceptionDefaultMessage()
    {
        try {
            $this->notifier->throwIrreversibleMigrationException();
            $this->fail();
        } catch (IrreversibleMigrationException $e) {
            $this->assertEquals('This migration is irreversible and cannot be reverted.', $e->getMessage());
        }
    }

    public function testWarnIf()
    {
        $this->outputWriter->expects($this->once())
            ->method('write')
            ->with('    <warning>test</warning>');

        $this->notifier->warnIf(true, 'test');
    }

    public function testAbortIf()
    {
        try {
            $this->notifier->abortIf(true, 'test');
            $this->fail();
        } catch (AbortMigrationException $e) {
            $this->assertEquals('test', $e->getMessage());
        }
    }

    public function testAbortIfDefaultMessage()
    {
        try {
            $this->notifier->abortIf(true);
            $this->fail();
        } catch (AbortMigrationException $e) {
            $this->assertEquals('Unknown Reason', $e->getMessage());
        }
    }

    public function testSkipIf()
    {
        try {
            $this->notifier->skipIf(true, 'test');
            $this->fail();
        } catch (SkipMigrationException $e) {
            $this->assertEquals('test', $e->getMessage());
        }
    }

    public function testSkipIfDefaultMessage()
    {
        try {
            $this->notifier->skipIf(true);
            $this->fail();
        } catch (SkipMigrationException $e) {
            $this->assertEquals('Unknown Reason', $e->getMessage());
        }
    }
}
