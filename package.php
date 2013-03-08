<?php

/**
 * Phing alternative to packaging the PHAR:
 *   $ php package.php
 *
 * @author Eric Clemmons <eric@smarterspam.com>
 */

$buildDir = realpath(dirname(__FILE__)) . '/build';

$pharName = "$buildDir/doctrine-migrations.phar";

if (!file_exists($buildDir)) {
    mkdir($buildDir);
}

if (file_exists($pharName)) {
    unlink($pharName);
}

$p = new Phar($pharName);
$p->CompressFiles(Phar::GZ);
$p->setSignatureAlgorithm(Phar::SHA1);

$p->startBuffering();

$dirs = array(
    './lib'                             =>  '/Doctrine\/Migrations/',
    './vendor/doctrine/dbal/lib'        =>  '/Doctrine\/DBAL/',
    './vendor/doctrine/common/lib'      =>  '/Doctrine\/Common/',
    './vendor/symfony/console'          =>  '/Symfony\/Component\/Console/',
    './vendor/symfony/finder'           =>  '/Symfony\/Component\/Finder/'
);

foreach ($dirs as $dir => $filter) {
    $p->buildFromDirectory($dir, $filter);
}

$p->stopBuffering();

$p->setStub(file_get_contents('phar-cli-stub.php'));
