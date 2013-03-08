<?php

require_once __DIR__ . '/../vendor/autoload.php';

call_user_func(function() {
    $loader = new \Composer\Autoload\ClassLoader();
    $loader->add('Migrations', __DIR__);
    $loader->register();
});

use Doctrine\Migrations\Executor;
use Doctrine\Migrations\Factory;
use Doctrine\Migrations\Loader;
use Doctrine\Migrations\Logger;
use Doctrine\Migrations\LoggerStorage\MongoDBLoggerStorage;
use Doctrine\Migrations\Manager;
use Doctrine\Migrations\Notifier;
use Doctrine\Migrations\OutputWriter;
use Doctrine\MongoDB\Connection;
use Symfony\Component\Console\Output\ConsoleOutput;

$connection = new Connection();

$consoleOutput = new ConsoleOutput();
$outputWriter = new OutputWriter(function($message) use ($consoleOutput) {
    $consoleOutput->writeln($message);
});
$notifier = new Notifier($outputWriter);

$factory = new Factory(function(\ReflectionClass $class) use ($notifier, $connection) {
    return $class->newInstance($notifier, $connection);
});

$loader = new Loader($factory);
$migrations = $loader->load(array(__DIR__.'/Migrations'));

$storage = new MongoDBLoggerStorage($connection->selectDatabase('test')->selectCollection('migrationLog'));
$logger = new Logger($storage);

$executor = new Executor($logger, $outputWriter);
$manager = new Manager($logger, $migrations, $executor);

return $manager;
