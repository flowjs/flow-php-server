<?php

require_once( __DIR__ . '/../vendor/autoload.php');
require_once( __DIR__ . '/../src/Resumable/Autoloader.php');

Resumable\Autoloader::register();

// register test classes
Resumable\Autoloader::register(__DIR__ . '/lib');