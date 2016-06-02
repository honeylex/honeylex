<?php

use Honeybee\FrameworkBinding\Silex\App;
use Honeybee\FrameworkBinding\Silex\ConfigHandler\ServiceConfigHandler;
use Silex\Provider\AssetServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Symfony\Component\Yaml\Parser;

$serviceConfigHandler = new ServiceConfigHandler(new Parser);
$serviceDefinitions = $serviceConfigHandler->handle(realpath(__DIR__ . '/config/services.yml'));

$app = new App($serviceDefinitions);
$app->register(new ServiceControllerServiceProvider());
$app->register(new AssetServiceProvider());
$app->register(new HttpFragmentServiceProvider());

return $app;
