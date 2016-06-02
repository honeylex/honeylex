<?php

namespace Honeybee\FrameworkBinding\Silex;

use Auryn\Injector;
use Auryn\StandardReflector;
use Honeybee\Common\Error\ConfigError;
use Honeybee\FrameworkBinding\Silex\Config\ConfigLoader;
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
    protected $configLoader;

    protected $crateLoader;

    public function __construct(ConfigLoader $configLoader, CrateLoaderInterface $crateLoader)
    {
        $this->configLoader = $configLoader;
        $this->crateLoader = $crateLoader;
    }

    public function __invoke(Application $app)
    {
        $crateMetadataMap = $this->configLoader->loadConfig('crates.yml');
        $crates = $this->crateLoader->loadCrates($app, $crateMetadataMap);

        $injector = new Injector(new StandardReflector);
        $serviceDefinitionMap = $this->configLoader->loadConfig('services.yml');
        $serviceProvisioner = new ServiceProvisioner($app, $injector, $serviceDefinitionMap);

        $app->register(new ServiceProvider($serviceProvisioner));
        $app->register(new ControllerResolverServiceProvider());
        $app->register(new AssetServiceProvider());
        $app->register(new HttpFragmentServiceProvider());

        return $app;
    }
}
