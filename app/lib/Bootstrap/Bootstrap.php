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

        $hostPrefix = $this->config->getHostPrefix();
        $appContext = $this->config->getAppContext();
        $appEnv = $this->config->getAppEnv();
        $app['version'] = $this->config->getVersion();
        $app['debug'] = $this->config->getAppDebug();
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

        // load project and host routing
        $localConfigDir = $this->config->getConfigDir().DIRECTORY_SEPARATOR;
        $routingFiles = [
            $localConfigDir.'routing.php',
            $localConfigDir."routing.$appContext.php",
            $localConfigDir."routing.$appEnv.php",
            $localConfigDir."routing.$appContext.$appEnv.php"
        ];
        if ($hostPrefix) {
            $hostConfigDir = $localConfigDir.$hostPrefix.DIRECTORY_SEPARATOR;
            $routingFiles[] = $hostConfigDir.'routing.php';
            $routingFiles[] = $hostConfigDir."routing.$appContext.php";
            $routingFiles[] = $hostConfigDir."routing.$appEnv.php";
            $routingFiles[] = $hostConfigDir."routing.$appContext.$appEnv.php";
        }

        foreach ($routingFiles as $routingFile) {
            $this->loadProjectRoutes($routingFile, $app);
        }

        return $app;
    }

    protected function loadProjectRoutes($routingFile, Application $routing)
    {
        if (is_readable($routingFile)) {
            $app = $routing;
            require $routingFile;
        }
    }

    protected function bootstrapConfig(Application $app, Injector $injector, array $settings)
    {
        $app['settings'] = $settings;
        $hostPrefix = $settings['hostPrefix'];
        $appContext = $settings['appContext'];
        $appEnv = $settings['appEnv'];
        $configDir = $settings['core']['config_dir'].DIRECTORY_SEPARATOR;
        $projectConfigDir = $settings['project']['config_dir'].DIRECTORY_SEPARATOR;

        // default configs
        $configHandlers = (new Parser)->parse(
            file_get_contents($configDir.'config_handlers.yml')
        );

        // project configs
        // @todo support project host specific config handlers
        $projectConfigHandlersFile = $projectConfigDir.'config_handlers.yml';
        if (is_readable($projectConfigHandlersFile)) {
            $configHandlers = array_merge(
                $configHandlers,
                (new Parser)->parse(file_get_contents($projectConfigHandlersFile))
            );
        }

        // project and host crate configs
        $cratesConfigFiles = [
            $projectConfigDir.'crates.yml',
            $projectConfigDir."crates.$appContext.yml",
            $projectConfigDir."crates.$appEnv.yml",
            $projectConfigDir."crates.$appContext.$appEnv.yml"
        ];
        if ($hostPrefix) {
            $hostConfigDir = $projectConfigDir.$hostPrefix.DIRECTORY_SEPARATOR;
            $cratesConfigFiles[] = $hostConfigDir.'crates.yml';
            $cratesConfigFiles[] = $hostConfigDir."crates.$appContext.yml";
            $cratesConfigFiles[] = $hostConfigDir."crates.$appEnv.yml";
            $cratesConfigFiles[] = $hostConfigDir."crates.$appContext.$appEnv.yml";
        }

        $crateManifestMap = (new CrateConfigHandler)->handle($cratesConfigFiles);
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

    protected function bootstrapSession(Application $app)
    {
        // sessions are started explicitly when required
        $app->register(new SessionServiceProvider);

        $app->before(function ($request) {
            $request->getSession()->start();
        });
    }
}
