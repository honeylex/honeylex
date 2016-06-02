<?php

use Auryn\Injector;
use Auryn\StandardReflector;
use Honeybee\FrameworkBinding\Silex\ConfigHandler\ServiceConfigHandler;
use Honeybee\FrameworkBinding\Silex\ServiceProvisioner;
use Symfony\Component\Debug\Debug;
use Symfony\Component\Yaml\Parser as YamlParser;

require_once __DIR__.'/../vendor/autoload.php';

$serviceConfigHandler = new ServiceConfigHandler(new YamlParser);
$serviceDefinitions = $serviceConfigHandler->handle(realpath(__DIR__ . '/../app/config/services.yml'));
$serviceProvisioner = new ServiceProvisioner(new Injector(new StandardReflector), $serviceDefinitions);
$serviceLocator = $serviceProvisioner->provision();

Debug::enable();

$app = require __DIR__.'/../app/bootstrap.php';
require __DIR__.'/../app/config/dev.php';
require __DIR__.'/../app/controllers.php';
$app->run();
