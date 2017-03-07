<?php

namespace Honeybee\FrameworkBinding\Silex\Bootstrap;

use Auryn\Injector;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Translation\TranslatorInterface;

class ApiBootstrap extends Bootstrap
{
    public function __invoke(Application $app, array $settings)
    {
        parent::__invoke($app, $settings);

        $this->registerTrustedProxies($app, (array)$this->config->getSetting('project.framework.trusted_proxies'));

        $this->registerErrorHandler($app, $this->injector);
        $this->registerViewHandler($app, $this->injector);

        return $app;
    }

    protected function registerErrorHandler(Application $app, Injector $injector)
    {
        $app->error(function (\Exception $e, Request $request, $code) use ($app, $injector) {
            $translator = $injector->make(TranslatorInterface::CLASS);
            $message = $e->getMessage();
            $message = $message ?: $e->getMessageKey();
            $errors = [
                'errors' => [
                    'code' => $code,
                    'message' => $translator->trans($message, [], 'errors')
                ]
            ];

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
