<?php

namespace Honeylex\Bootstrap;

use Auryn\Injector;
use Silex\Application;
use Silex\Provider\WebProfilerServiceProvider;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WebBootstrap extends Bootstrap
{
    public function __invoke(Application $app, array $settings)
    {
        // start Symfony debug early for web context
        if ($settings['appDebug']) {
            Debug::enable();
        }

        parent::__invoke($app, $settings);

        $this->registerTrustedProxies($app, (array)$this->config->getSetting('project.framework.trusted_proxies'));

        if ($this->config->getSetting('project.session.auto_start', true)) {
            $this->bootstrapSession($app);
        }

        if ($app['debug']) {
            $app->register(
                new WebProfilerServiceProvider,
                [ 'profiler.cache_dir' => $this->config->getProjectDir().'/var/cache/profiler' ]
            );
        }

        $this->registerErrorHandler($app, $this->injector);
        $this->registerViewHandler($app, $this->injector);

        return $app;
    }

    protected function registerErrorHandler(Application $app, Injector $injector)
    {
        $app->error(function (\Exception $e, Request $request, $code) use ($app) {
            $message = $e->getMessage();
            $message = $message ?: $e->getMessageKey();
            $errors = [ 'errors' => [ 'code' => $code, 'message' => $message ] ];

            if ($app['debug']) {
                return;
            }

            $templates = [
                'errors/'.$code.'.html.twig',
                'errors/'.substr($code, 0, 2).'x.html.twig',
                'errors/'.substr($code, 0, 1).'xx.html.twig',
                'errors/default.html.twig',
            ];

            return new Response(
                $app['twig']->resolveTemplate($templates)->render($errors),
                $code
            );
        });
    }

    protected function registerViewHandler(Application $app, Injector $injector)
    {
        $app->view(function (array $controllerResult, Request $request) use ($app, $injector) {
            $view = $injector->make($controllerResult[0]);
            return $view->renderHtml($request, $app);
        });
    }
}
