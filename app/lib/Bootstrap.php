<?php

namespace Honeybee\FrameworkBinding\Silex;

use Auryn\Injector;
use Auryn\StandardReflector;
use Honeybee\Common\Error\ConfigError;
use Honeybee\FrameworkBinding\Silex\Controller\ControllerResolverServiceProvider;
use Honeybee\FrameworkBinding\Silex\Crate\CrateLoaderInterface;
use Honeybee\FrameworkBinding\Silex\Crate\CrateMap;
use Honeybee\FrameworkBinding\Silex\Crate\CrateMetadataMap;
use Honeybee\FrameworkBinding\Silex\Service\ServiceProvider;
use Honeybee\FrameworkBinding\Silex\Service\ServiceProvisioner;
use Honeybee\Infrastructure\Config\ConfigInterface;
use Honeybee\ServiceDefinitionMap;
use Pimple\Container;
use Silex\Application;
use Silex\Provider\AssetServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;

class Bootstrap
{
    protected $config;

    protected $crateLoader;

    public function __construct(ConfigInterface $config, CrateLoaderInterface $crateLoader)
    {
        $this->config = $config;
        $this->crateLoader = $crateLoader;
    }

    public function __invoke(CrateMetadataMap $crateMetadataMap, ServiceDefinitionMap $serviceDefinitions)
    {
        $appClass = $this->config->get('app', Application::CLASS);
        if (!class_exists($appClass)) {
            throw new ConfigError('Unable to load configured application class: ' . $appClass);
        }

        $app = new $appClass();
        $crates = $this->crateLoader->loadCrates($app, $crateMetadataMap);

        return $this->registerServices($app, $crates, $serviceDefinitions);
    }

    protected function registerServices(Container $app, CrateMap $crates, ServiceDefinitionMap $serviceDefinitions)
    {
        $injector = new Injector(new StandardReflector);
        $serviceProvisioner = new ServiceProvisioner($app, $injector, $serviceDefinitions);

        $app->register(new ServiceProvider($serviceProvisioner));
        $app->register(new ControllerResolverServiceProvider());
        $app->register(new AssetServiceProvider());
        $app->register(new HttpFragmentServiceProvider());

        return $app;
    }
}
