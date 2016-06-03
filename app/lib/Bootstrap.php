<?php

namespace Honeybee\FrameworkBinding\Silex;

use Auryn\Injector;
use Auryn\StandardReflector;
use Honeybee\FrameworkBinding\Silex\Config\ConfigLoader;
use Honeybee\FrameworkBinding\Silex\Config\ConfigProvider;
use Honeybee\FrameworkBinding\Silex\Controller\ControllerResolverServiceProvider;
use Honeybee\FrameworkBinding\Silex\Crate\CrateLoaderInterface;
use Honeybee\FrameworkBinding\Silex\Service\ServiceProvider;
use Honeybee\FrameworkBinding\Silex\Service\ServiceProvisioner;
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
        $crateManifestMap = $this->configLoader->loadConfig('crates.yml');
        $crateMap = $this->crateLoader->loadCrates($app, $crateManifestMap);
        $configProvider = new ConfigProvider($this->configLoader, $crateMap);

        $injector = new Injector(new StandardReflector);
        $serviceDefinitionMap = $configProvider->provide('services.yml');
        $serviceProvisioner = new ServiceProvisioner($app, $injector, $configProvider, $serviceDefinitionMap);

        $app->register(new ServiceProvider($serviceProvisioner));
        $app->register(new ControllerResolverServiceProvider());
        $app->register(new AssetServiceProvider());
        $app->register(new HttpFragmentServiceProvider());

        return $app;
    }
}
