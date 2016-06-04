<?php

namespace Honeybee\FrameworkBinding\Silex;

use Auryn\Injector;
use Auryn\StandardReflector;
use Honeybee\FrameworkBinding\Silex\Config\ConfigLoader;
use Honeybee\FrameworkBinding\Silex\Config\ConfigProvider;
use Honeybee\FrameworkBinding\Silex\Config\ConfigProviderInterface;
use Honeybee\FrameworkBinding\Silex\Controller\ControllerResolverServiceProvider;
use Honeybee\FrameworkBinding\Silex\Crate\CrateLoaderInterface;
use Honeybee\FrameworkBinding\Silex\Service\ServiceProvider;
use Honeybee\FrameworkBinding\Silex\Service\ServiceProvisioner;
use Psr\Log\LoggerInterface;
use Silex\Application;
use Silex\Provider\AssetServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\WebProfilerServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        // load crates and init config-provider
        $crateManifestMap = $this->configLoader->loadConfig('crates.yml');
        $crateMap = $this->crateLoader->loadCrates($app, $crateManifestMap);
        $configProvider = new ConfigProvider($this->configLoader, $crateMap);
        // register logger as first item within the DI chain
        $app->register(new MonologServiceProvider(), [
            'monolog.logfile' => $configProvider->getProjectDir().'/var/logs/silex_dev.log'
        ]);
        $logger = $app['logger'];
        $injector = new Injector(new StandardReflector);
        $injector
            ->share($logger)
            ->alias(LoggerInterface::CLASS, get_class($logger));
        // then kick off service provisioning
        $injector->share($configProvider)->alias(ConfigProviderInterface::CLASS, get_class($configProvider));
        $serviceDefinitionMap = $configProvider->provide('services.yml');
        $serviceProvisioner = new ServiceProvisioner($app, $injector, $configProvider, $serviceDefinitionMap);
        // and register some standard service providers.
        $app['version'] = $configProvider->getVersion();
        $app->register(new ServiceProvider($serviceProvisioner));
        $app->register(new ControllerResolverServiceProvider);
        $app->register(new AssetServiceProvider);
        $app->register(new HttpFragmentServiceProvider);
        // load context specific configuration (well, only web atm. needs to change too)
        if ($configProvider->getAppContext() === 'web') {
            $this->registerWebErrorHandler($app);
            $this->loadProjectRoutes($configProvider->getConfigDir().'/routing.php', $app);
            // dev specific switches
            if ($configProvider->getAppEnv() === 'dev') {
                $app['debug'] = true;
                $app->register(
                    new WebProfilerServiceProvider(),
                    [ 'profiler.cache_dir' => $configProvider->getProjectDir().'/var/cache/profiler' ]
                );
            }
        }
        // load environment specific configuration (this has to change badly)
        $envConfig = $configProvider->getEnvConfigPath();
        if (is_readable($envConfig)) {
            require $envConfig;
        }

        return $app;
    }

    protected function loadProjectRoutes($routesFile, Application $routing)
    {
        if (is_readable($routesFile)) {
            $app = $routing;
            require $routesFile;
        }
    }

    protected function registerWebErrorHandler(Application $app)
    {
        $app->error(function (\Exception $e, Request $request, $code) use ($app) {
            if ($app['debug']) {
                return;
            }
            // 404.html, or 40x.html, or 4xx.html, or error.html
            $templates = [
                'errors/' . $code . '.html.twig',
                'errors/' . substr($code, 0, 2) . 'x.html.twig',
                'errors/' . substr($code, 0, 1) . 'xx.html.twig',
                'errors/default.html.twig',
            ];

            return new Response(
                $app['twig']->resolveTemplate($templates)->render([ 'code' => $code ]),
                $code
            );
        });
    }
}
