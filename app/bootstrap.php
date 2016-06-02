<?php

use Honeybee\FrameworkBinding\Silex\Bootstrap;
use Honeybee\FrameworkBinding\Silex\Config\ConfigLoader;
use Honeybee\FrameworkBinding\Silex\Crate\CrateLoader;
use Honeybee\Infrastructure\Config\ArrayConfig;
use Silex\Application;
use Symfony\Component\Yaml\Parser;

$loaderConfig = (new Parser)->parse(file_get_contents(__DIR__ . '/config/configs.yml'));
$loaderConfig['core.config_dir'] = __DIR__ . '/config';
$configLoader = new ConfigLoader(new ArrayConfig($loaderConfig));

$bootstrap = new Bootstrap($configLoader, new CrateLoader);
$app = $bootstrap(new Application);

require __DIR__ . '/config/' . $appEnv . '.php';

return $app;
