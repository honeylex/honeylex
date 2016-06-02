<?php

use Honeybee\FrameworkBinding\Silex\Bootstrap;
use Honeybee\FrameworkBinding\Silex\ConfigHandler\CrateConfigHandler;
use Honeybee\FrameworkBinding\Silex\ConfigHandler\ServiceConfigHandler;
use Honeybee\FrameworkBinding\Silex\Crate\CrateLoader;
use Honeybee\Infrastructure\Config\ArrayConfig;
use Silex\Application;
use Symfony\Component\Yaml\Parser;

$serviceConfigHandler = new ServiceConfigHandler(new Parser);
$serviceDefinitionMap = $serviceConfigHandler->handle(__DIR__ . '/config/services.yml');

$crateConfigHandler = new CrateConfigHandler(new Parser);
$crateMetadataMap = $crateConfigHandler->handle(__DIR__ . '/config/crates.yml');

$bootstrapConfig = new ArrayConfig([ 'app' => Application::CLASS ]);
$bootstrap = new Bootstrap($bootstrapConfig, new CrateLoader());

$app = $bootstrap($crateMetadataMap, $serviceDefinitionMap);

require __DIR__ . '/config/' . $appEnv . '.php';

return $app;
