<?php

namespace Honeybee\FrameworkBinding\Silex\Bootstrap;

use Auryn\Injector;
use Auryn\StandardReflector;
use Honeybee\FrameworkBinding\Silex\Config\ConfigProvider;
use Honeybee\FrameworkBinding\Silex\Config\ConfigProviderInterface;
use Honeybee\FrameworkBinding\Silex\Config\Handler\CrateConfigHandler;
use Honeybee\FrameworkBinding\Silex\Controller\ControllerResolverServiceProvider;
use Honeybee\FrameworkBinding\Silex\Crate\CrateLoader;
use Honeybee\FrameworkBinding\Silex\Crate\CrateMap;
use Honeybee\FrameworkBinding\Silex\Service\ServiceProvider;
use Honeybee\FrameworkBinding\Silex\Service\ServiceProvisioner;
use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\Config\Settings;
use Psr\Log\LoggerInterface;
use Silex\Application;
use Silex\Provider\AssetServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Parser;

class Bootstrap
{
    protected $injector;

    protected $config;

    public function __construct()
    {
        $this->injector = new Injector(new StandardReflector);
    }

    public function __invoke(Application $app, array $settings)
    {
        $this->config = $this->bootstrapConfig($app, $this->injector, $settings);

        $app['version'] = $this->config->getVersion();
        $app['debug'] = $this->config->getAppEnv() === 'development';
        $this->bootstrapLogger($app, $this->config, $this->injector);

        // then kick off service provisioning
        $serviceProvisioner = new ServiceProvisioner($app, $this->injector, $this->config);

        // and register some standard service providers.
        $app->register(new ServiceProvider($serviceProvisioner));
        $app->register(new ControllerResolverServiceProvider);
        $app->register(new AssetServiceProvider);
        $app->register(new HttpFragmentServiceProvider);
        $app->register(new FormServiceProvider);
        $app->register(new ValidatorServiceProvider);
        $app->register(new SessionServiceProvider);

        $localConfigDir = $this->config->getConfigDir();
        $appContext = $this->config->getAppContext();
        $this->loadProjectRoutes($localConfigDir.'/routing.php', $app);
        $this->loadProjectRoutes($localConfigDir."/routing/$appContext.php", $app);

        return $app;
    }

    protected function loadProjectRoutes($routesFile, Application $routing)
    {
        if (is_readable($routesFile)) {
            $app = $routing;
            require $routesFile;
        }
    }

    protected function bootstrapConfig(Application $app, Injector $injector, array $settings)
    {
        $app['settings'] = $settings;
        $appContext = $settings['appContext'];
        $configDir = $settings['core']['config_dir'];
        $projectConfigDir = $settings['project']['config_dir'];

        // default configs
        $configHandlers = (new Parser)->parse(
            file_get_contents($configDir.'/config_handlers.yml')
        );

        // project configs
        $projectConfigHandlersFile = $projectConfigDir.'/config_handlers.yml';
        if (is_readable($projectConfigHandlersFile)) {
            $configHandlers = array_merge(
                $configHandlers,
                (new Parser)->parse(file_get_contents($projectConfigHandlersFile))
            );
        }

        // crate configs
        $cratesConfigFiles = [
            $projectConfigDir.'/crates.yml',
            $projectConfigDir."/crates/$appContext.yml"
        ];
        $crateManifestMap =  (new CrateConfigHandler)->handle($cratesConfigFiles);
        $crateMap = $crateManifestMap->count() > 0
            ? (new CrateLoader)->loadCrates($app, $crateManifestMap)
            : new CrateMap;

        // load crates and init config-provider
        $config = new ConfigProvider(new Settings($settings), $crateMap, new ArrayConfig($configHandlers), new Finder);
        $injector->share($config)->alias(ConfigProviderInterface::CLASS, get_class($config));

        return $config;
    }

    protected function bootstrapLogger(Application $app, ConfigProviderInterface $config, Injector $injector)
    {
        // register logger as first item within the DI chain
        $app->register(new MonologServiceProvider, [
            'monolog.logfile' => $config->getProjectDir().'/var/logs/honeylex.log'
        ]);

        $logger = $app['logger'];

        $injector
            ->share($logger)
            ->alias(LoggerInterface::CLASS, get_class($logger));

        return $logger;
    }
}
