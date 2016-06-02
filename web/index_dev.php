<?php

use Symfony\Component\Debug\Debug;

require_once __DIR__.'/../vendor/autoload.php';

Debug::enable();

$app = require __DIR__.'/../app/bootstrap.php';
require __DIR__.'/../app/config/dev.php';
require __DIR__.'/../app/config/routing.php';
$app->run();
