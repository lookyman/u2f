<?php

use Nette\Caching\Storages\DevNullStorage;
use Nette\Loaders\RobotLoader;

require_once __DIR__ . '/../vendor/autoload.php';

$loader = new RobotLoader;
$loader->autoRebuild = TRUE;
$loader->setCacheStorage(new DevNullStorage)
	->addDirectory(__DIR__)
	->register();

define('TEMP_DIR', __DIR__ . '/tmp');
