<?php

$appEnv = 'dev';

require_once __DIR__.'/../vendor/autoload.php';

Symfony\Component\Debug\Debug::enable();

$app = require __DIR__.'/../app/bootstrap.php';
require __DIR__.'/../app/config/routing.php';

$app->run();
