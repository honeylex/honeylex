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

    public function __invoke(Application $app, $appContext, $appEnv)
    {
        $crateManifestMap = $this->configLoader->loadConfig('crates.yml');
        $crateMap = $this->crateLoader->loadCrates($app, $crateManifestMap);
        $configProvider = new ConfigProvider($this->configLoader, $crateMap);

        $injector = new Injector(new StandardReflector);
        $serviceDefinitionMap = $configProvider->provide('services.yml');
        $serviceProvisioner = new ServiceProvisioner($app, $injector, $configProvider, $serviceDefinitionMap);

        $app->register(new ServiceProvider($serviceProvisioner));
        $app->register(new ControllerResolverServiceProvider);
        $app->register(new AssetServiceProvider);
        $app->register(new HttpFragmentServiceProvider);

        $envConfig = dirname(__DIR__).'/config/'.$appEnv.'.php';
        if ($envConfig) {
            require $envConfig;
        }
        if ($appContext === 'web') {
            $this->registerWebErrorHandler($app);
            $projectRouting = dirname(__DIR__).'/config/routing.php';
            if (is_readable($projectRouting)) {
                require $projectRouting;
            }
        }

        return $app;
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
