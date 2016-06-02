<?php

use Auryn\Injector;
use Auryn\StandardReflector;
use Honeybee\FrameworkBinding\Silex\App;
use Honeybee\FrameworkBinding\Silex\ConfigHandler\ServiceConfigHandler;
use Honeybee\FrameworkBinding\Silex\ServiceProvisioner;
use Silex\Provider\AssetServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\Yaml\Parser;

$serviceConfigHandler = new ServiceConfigHandler(new Parser);
$serviceDefinitions = $serviceConfigHandler->handle(realpath(__DIR__ . '/config/services.yml'));
$serviceProvisioner = new ServiceProvisioner(new Injector(new StandardReflector), $serviceDefinitions);

$app = new App($serviceProvisioner->provision());
$app->register(new ServiceControllerServiceProvider());
$app->register(new AssetServiceProvider());
$app->register(new TwigServiceProvider());
$app->register(new HttpFragmentServiceProvider());

$app['twig'] = $app->extend('twig', function ($twig, $app) {
    // add custom globals, filters, tags, ...
    return $twig;
});

return $app;
