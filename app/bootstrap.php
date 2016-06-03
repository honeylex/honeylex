<?php

use Honeybee\FrameworkBinding\Silex\Bootstrap;
use Honeybee\FrameworkBinding\Silex\Config\ConfigLoader;
use Honeybee\FrameworkBinding\Silex\Crate\CrateLoader;
use Honeybee\Infrastructure\Config\ArrayConfig;
use Silex\Application;
use Symfony\Component\Yaml\Parser;

$loaderConfig = (new Parser)->parse(file_get_contents(__DIR__.'/config/configs.yml'));
$loaderConfig = array_merge(
    $loaderConfig, // @todo figure out if we are loaded within vendor and adjust dynamically
    [
        'core.config_dir' => __DIR__.'/config',
        'project.config_dir' => __DIR__.'/config'
    ]
);
$configLoader = new ConfigLoader($appContext, $appEnv, new ArrayConfig($loaderConfig));
$bootstrap = new Bootstrap($configLoader, new CrateLoader);
$app = $bootstrap(new Application);

return $app;
