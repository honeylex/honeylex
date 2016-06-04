<?php

use Honeybee\FrameworkBinding\Silex\Bootstrap;
use Honeybee\FrameworkBinding\Silex\Config\ConfigLoader;
use Honeybee\FrameworkBinding\Silex\Crate\CrateLoader;
use Honeybee\Infrastructure\Config\ArrayConfig;
use Silex\Application;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Parser;

$loaderConfig = (new Parser)->parse(file_get_contents(__DIR__.'/config/configs.yml'));
$loaderConfig = array_merge(
    $loaderConfig, // @todo figure out if we are loaded within vendor and adjust dynamically
    [
        'core.config_dir' => __DIR__.'/config',
        'core.dir' => dirname(__DIR__),
        'project.dir' => dirname(__DIR__),
        'project.config_dir' => __DIR__.'/config'
    ]
);
$configLoader = new ConfigLoader($appContext, $appEnv, new ArrayConfig($loaderConfig), new Finder);
$bootstrap = new Bootstrap($configLoader, new CrateLoader);
$app = $bootstrap(new Application);

return $app;
