<?php

namespace Doctrine\Migrations\Tests;

use Doctrine\Migrations\Executor;
use Doctrine\Migrations\Factory;
use Doctrine\Migrations\Loader;
use Doctrine\Migrations\Logger;
use Doctrine\Migrations\LoggerStorage\MongoDBLoggerStorage;
use Doctrine\Migrations\Manager;
use Doctrine\Migrations\Migrations;
use Doctrine\Migrations\Migration\AbstractDBALMigration;
use Doctrine\Migrations\Notifier;
use Doctrine\Migrations\OutputWriter;
use Doctrine\MongoDB\Connection as MongoDBConnection;
use Doctrine\DBAL\Configuration as DBALConfiguration;
use Doctrine\DBAL\DriverManager as DBALDriverManager;

class FunctionalTest extends \PHPUnit_Framework_TestCase
{
    private $manager;
    private $mysqlConnection;
    private $logger;

    protected function setUp()
    {
        $params = array(
            'driver' => 'pdo_mysql',
            'user' => 'root'
        );
        $config = new DBALConfiguration();
        $mysqlConnection = DBALDriverManager::getConnection($params, $config);
        $mysqlConnection->exec('CREATE DATABASE testdb;');

        $params = array(
            'driver' => 'pdo_mysql',
            'user' => 'root',
            'dbname' => 'testdb'
        );
        $config = new DBALConfiguration();
        $this->mysqlConnection = DBALDriverManager::getConnection($params, $config);
        $mongodbConnection = new MongoDBConnection();

        $outputWriter = new OutputWriter();
        $notifier = new Notifier($outputWriter);

        $mysqlConnection = $this->mysqlConnection;
        $factory = new Factory(function(\ReflectionClass $class) use ($notifier, $mysqlConnection) {
            return $class->newInstance($notifier, $mysqlConnection);
        });

        $storage = new MongoDBLoggerStorage($mongodbConnection->selectDatabase('test')->selectCollection('migrationLog'));
        $this->logger = new Logger($storage);

        $migrations = new Migrations();
        $migrations[] = $factory->newInstance(new \ReflectionClass(__NAMESPACE__.'\Migration1'));
        $migrations[] = $factory->newInstance(new \ReflectionClass(__NAMESPACE__.'\Migration2'));
        $migrations[] = $factory->newInstance(new \ReflectionClass(__NAMESPACE__.'\Migration3'));
        $migrations[] = $factory->newInstance(new \ReflectionClass(__NAMESPACE__.'\Migration4'));

        $executor = new Executor($this->logger, $outputWriter);
        $this->manager = new Manager($this->logger, $migrations, $executor);
    }

    protected function tearDown()
    {
        $mongodbConnection = new MongoDBConnection();
        $mongodbConnection
            ->selectDatabase('test')
            ->selectCollection('migrationLog')
            ->drop();

        $params = array(
            'driver' => 'pdo_mysql',
            'user' => 'root'
        );
        $config = new DBALConfiguration();
        $mysqlConnection = DBALDriverManager::getConnection($params, $config);
        $mysqlConnection->exec('DROP DATABASE testdb;');
    }

    public function testMigrateUp()
    {
        $this->assertFalse($this->manager->isVersionMigrated(1));
        $this->assertFalse($this->manager->isVersionMigrated(2));
        $this->assertFalse($this->manager->isVersionMigrated(3));
        $this->assertFalse($this->manager->isVersionMigrated(4));

        $this->manager->migrate();

        $this->assertTrue($this->manager->isVersionMigrated(1));
        $this->assertTrue($this->manager->isVersionMigrated(2));
        $this->assertTrue($this->manager->isVersionMigrated(3));
        $this->assertTrue($this->manager->isVersionMigrated(4));
    }

    public function testMigrateDown()
    {
        $this->manager->migrate();

        $this->assertTrue($this->manager->isVersionMigrated(1));
        $this->assertTrue($this->manager->isVersionMigrated(2));
        $this->assertTrue($this->manager->isVersionMigrated(3));
        $this->assertTrue($this->manager->isVersionMigrated(4));

        $this->manager->migrate(0);

        $this->assertFalse($this->manager->isVersionMigrated(1));
        $this->assertFalse($this->manager->isVersionMigrated(2));
        $this->assertFalse($this->manager->isVersionMigrated(3));
        $this->assertFalse($this->manager->isVersionMigrated(4));
    }

    public function testSkipMigrateUp()
    {
        $this->manager->migrate();

        $this->assertTrue($this->manager->isVersionMigrated(1));
        $this->assertTrue($this->manager->isVersionMigrated(2));
        $this->assertTrue($this->manager->isVersionMigrated(3));
        $this->assertTrue($this->manager->isVersionMigrated(4));

        $schema = $this->mysqlConnection->getSchemaManager()->createSchema();
        $this->assertTrue($schema->hasTable('migration1'));
        $this->assertTrue($schema->hasTable('migration2'));
        $this->assertFalse($schema->hasTable('migration3'));
        $this->assertTrue($schema->hasTable('migration4'));
    }

    public function testMigrateSeveralSteps()
    {
        $this->manager->migrate(3);

        $schema = $this->mysqlConnection->getSchemaManager()->createSchema();
        $this->assertTrue($schema->hasTable('migration1'));
        $this->assertTrue($schema->hasTable('migration2'));
        $this->assertFalse($schema->hasTable('migration3'));
        $this->assertFalse($schema->hasTable('migration4'));

        $this->assertTrue($this->manager->isVersionMigrated(1));
        $this->assertTrue($this->manager->isVersionMigrated(2));
        $this->assertTrue($this->manager->isVersionMigrated(3));
        $this->assertFalse($this->manager->isVersionMigrated(4));

        $this->manager->migrate(4);

        $schema = $this->mysqlConnection->getSchemaManager()->createSchema();
        $this->assertTrue($schema->hasTable('migration4'));
        $this->assertTrue($this->manager->isVersionMigrated(4));

        $this->manager->migrate(3);

        $schema = $this->mysqlConnection->getSchemaManager()->createSchema();

        $this->assertFalse($this->manager->isVersionMigrated(4));
        $this->assertTrue($schema->hasTable('migration1'));
        $this->assertTrue($schema->hasTable('migration2'));
        $this->assertFalse($schema->hasTable('migration3'));
        $this->assertFalse($schema->hasTable('migration4'));

        $this->manager->migrate(0);

        $schema = $this->mysqlConnection->getSchemaManager()->createSchema();

        $this->assertFalse($this->manager->isVersionMigrated(1));
        $this->assertFalse($this->manager->isVersionMigrated(2));
        $this->assertFalse($this->manager->isVersionMigrated(3));
        $this->assertFalse($this->manager->isVersionMigrated(4));
        $this->assertFalse($schema->hasTable('migration1'));
        $this->assertFalse($schema->hasTable('migration2'));
        $this->assertFalse($schema->hasTable('migration3'));
        $this->assertFalse($schema->hasTable('migration4'));
    }

    public function testVersionInDatabaseWithoutRegisteredMigrationStillMigrates()
    {
        $this->logger->logUp(25);

        $this->manager->migrate();

        $this->assertTrue($this->manager->isVersionMigrated(1));
        $this->assertTrue($this->manager->isVersionMigrated(2));
        $this->assertTrue($this->manager->isVersionMigrated(3));
        $this->assertTrue($this->manager->isVersionMigrated(4));
    }

    public function testInterweavedMigrationsAreExecuted()
    {
        $this->logger->logUp(1);
        $this->logger->logUp(4);

        $this->manager->migrate();

        $this->assertTrue($this->manager->isVersionMigrated(1));
        $this->assertTrue($this->manager->isVersionMigrated(2));
        $this->assertTrue($this->manager->isVersionMigrated(3));
        $this->assertTrue($this->manager->isVersionMigrated(4));
    }
}

class Migration1 extends AbstractDBALMigration
{
    public function getVersion()
    {
        return 1;
    }

    public function up()
    {
        $this->addSql("CREATE TABLE migration1 (test varchar(255))");
    }

    public function down()
    {
        $this->addSql("DROP TABLE migration1");
    }
}

class Migration2 extends AbstractDBALMigration
{
    public function getVersion()
    {
        return 2;
    }

    public function up()
    {
        $this->addSql("CREATE TABLE migration2 (test varchar(255))");
    }

    public function down()
    {
        $this->addSql("DROP TABLE migration2");
    }
}

class Migration3 extends AbstractDBALMigration
{
    public function getVersion()
    {
        return 3;
    }

    public function up()
    {
        $this->addSql("CREATE TABLE migration3 (test varchar(255))");
    }

    public function down()
    {
        $this->addSql("DROP TABLE migration3");
    }

    public function preUp()
    {
        $this->getNotifier()->skipIf(true);
    }

    public function preDown()
    {
        $this->getNotifier()->skipIf(true);
    }
}

class Migration4 extends AbstractDBALMigration
{
    public function getVersion()
    {
        return 4;
    }

    public function up()
    {
        $this->addSql("CREATE TABLE migration4 (test varchar(255))");
    }

    public function down()
    {
        $this->addSql("DROP TABLE migration4");
    }
}
