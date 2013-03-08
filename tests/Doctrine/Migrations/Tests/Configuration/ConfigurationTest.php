<?php

namespace Doctrine\Migrations\Tests\Configuration;

use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Configuration\XmlConfiguration;
use Doctrine\Migrations\Configuration\YamlConfiguration;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getTestFileConfigurationData
     */
    public function testFileConfiguration($configuration, $file)
    {
        $configuration->load($file);
        $this->assertEquals('Doctrine Migrations Library Test', $configuration->getName());
        $this->assertEquals('/path/to/migrations/classes/DoctrineMigrations', $configuration->getMigrationsDirectory());
        $this->assertEquals('DoctrineMigrationsTest', $configuration->getMigrationsNamespace());
        $this->assertEquals('bootstrap.php', $configuration->getBootstrapFile());
    }

    public function getTestFileConfigurationData()
    {
        return array(
            array(new XmlConfiguration(), __DIR__.'/../../../../Fixtures/Configuration/XML/configuration.xml'),
            array(new YamlConfiguration(), __DIR__.'/../../../../Fixtures/Configuration/Yaml/configuration.yml')
        );
    }

    public function testValidate()
    {
        $configuration = new Configuration();
        $configuration->setMigrationsNamespace('Namespace');
        $configuration->setMigrationsDirectory('/path/to/migrations');
        $configuration->setBootstrapFile('bootstrap.php');
        $configuration->validate();
    }

    /**
     * @expectedException Doctrine\Migrations\MigrationException
     */
    public function testValidateThrowsException()
    {
        $configuration = new Configuration();
        $configuration->validate();
    }
}
