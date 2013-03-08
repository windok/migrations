<?php

namespace Doctrine\Migrations\Tests;

use Doctrine\Migrations\OutputWriter;

class OutputWriterTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultConstruct()
    {
        $outputWriter = new OutputWriter();
        $outputWriter->write('test');
    }

    public function testWrite()
    {
        $test = false;
        $closure = function($message) use (&$test) {
            $test = $message;
        };
        $outputWriter = new OutputWriter($closure);
        $outputWriter->write(true);
        $this->assertTrue($test);
    }
}
