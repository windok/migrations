<?php

namespace Doctrine\Migrations\Tests;

use Doctrine\Migrations\Logger;
use Doctrine\Migrations\LoggerStorageInterface;

class LoggerTest extends \PHPUnit_Framework_TestCase
{
    private $storage;
    private $logger;

    protected function setUp()
    {
        $this->storage = new ArrayLoggerStorage();
        $this->logger = new Logger($this->storage);
    }

    public function testLogUp()
    {
        $this->logger->logUp(123);
        $this->logger->logUp(124);
        $logs = $this->storage->getLogs();
        $this->assertEquals(2, count($logs));

        $this->assertEquals('up', $logs[0]['direction']);
        $this->assertEquals(123, $logs[0]['version']);
        $this->assertEquals(false, $logs[0]['manual']);

        $this->assertEquals('up', $logs[1]['direction']);
        $this->assertEquals(124, $logs[1]['version']);
        $this->assertEquals(false, $logs[1]['manual']);
    }

    public function testLogDown()
    {
        $this->logger->logDown(123);
        $this->logger->logDown(124);
        $logs = $this->storage->getLogs();
        $this->assertEquals(2, count($logs));

        $this->assertEquals('down', $logs[0]['direction']);
        $this->assertEquals(123, $logs[0]['version']);
        $this->assertEquals(false, $logs[0]['manual']);

        $this->assertEquals('down', $logs[1]['direction']);
        $this->assertEquals(124, $logs[1]['version']);
        $this->assertEquals(false, $logs[1]['manual']);
    }

    public function testGetCurrentVersion()
    {
        $this->logger->logUp(123);
        $this->assertEquals(123, $this->logger->getCurrentVersion());

        $this->logger->logUp(124);
        $this->assertEquals(124, $this->logger->getCurrentVersion());

        $this->logger->logDown(124);
        $this->assertEquals(123, $this->logger->getCurrentVersion());

        $this->logger->logDown(123);
        $this->assertEquals(0, $this->logger->getCurrentVersion());
    }

    public function testGetUpLogs()
    {
        $this->logger->logUp(123);
        $this->logger->logUp(124);
        $this->logger->logDown(124);

        $upLogs = $this->logger->getUpLogs();
        $this->assertEquals(1, count($upLogs));
        $this->assertTrue(isset($upLogs[123]));
    }
}
