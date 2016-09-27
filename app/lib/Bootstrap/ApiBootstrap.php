<?php

namespace Honeybee\FrameworkBinding\Silex\Bootstrap;

use Auryn\Injector;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiBootstrap extends Bootstrap
{
    public function __invoke(Application $app, array $settings)
    {
        parent::__invoke($app, $settings);

        $this->registerErrorHandler($app);
        $this->registerViewHandler($app, $this->injector);

        return $app;
    }

    protected function registerErrorHandler(Application $app)
    {
        $app->error(function (\Exception $e, Request $request, $code) use ($app) {
            $message = $e->getMessage();
            $message = $message ?: $e->getMessageKey();
            $errors = [ 'errors' => [ 'code' => $code, 'message' => $message ] ];

            // @todo translate response
            return new JsonResponse($errors, $code);
        });
    }

    protected function registerViewHandler(Application $app, Injector $injector)
    {
        $app->view(function (array $controllerResult, Request $request) use ($app, $injector) {
            $view = $injector->make($controllerResult[0]);
            return $view->renderJson($request, $app);
        });
    }
}
