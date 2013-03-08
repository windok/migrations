<?php

namespace Doctrine\Migrations\Tests;

use Doctrine\Migrations\LoggerStorageInterface;

class ArrayLoggerStorage implements LoggerStorageInterface
{
    private $logs = array();

    public function save(array $data)
    {
        $this->logs[] = $data;
    }

    public function getLogs()
    {
        return $this->logs;
    }
}
