<?php

namespace Doctrine\Migrations\Tests;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Tools;

class ToolsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getTestExecutionStateData
     */
    public function testGetExecutionState($state, $expected)
    {
        $this->assertEquals($expected, Tools::getExecutionState($state));
    }

    public function getTestExecutionStateData()
    {
        return array(
            array(AbstractMigration::STATE_PRE, 'Pre-Checks'),
            array(AbstractMigration::STATE_POST, 'Post-Checks'),
            array(AbstractMigration::STATE_EXEC, 'Execution'),
            array(AbstractMigration::STATE_NONE, 'No State')
        );
    }

    /**
     * @dataProvider getTestFormatVersionData
     */
    public function testFormatVersion($version, $expected)
    {
        $this->assertEquals($expected, Tools::formatVersion($version));
    }

    public function getTestFormatVersionData()
    {
        return array(
            array('20040101000000', '2004-01-01 00:00:00'),
            array('20120101012515', '2012-01-01 01:25:15'),
            array('1234', '1234'),
            array(null, '')
        );
    }

    /**
     * @dataProvider getTestGetDirectoryRelativeToFileData
     */
    public function testGetDirectoryRelativeToFile($file, $input, $expected)
    {
        $this->assertEquals($expected, Tools::getDirectoryRelativeToFile($file, $input));
    }

    public function getTestGetDirectoryRelativeToFileData()
    {
        return array(
            array(__DIR__.'/ToolsTest.php', 'OutputWriterTest.php', __DIR__.'/OutputWriterTest.php'),
            array(__DIR__.'/ToolsTest.php', 'Test.php', 'Test.php')
        );
    }
}
