<?php

namespace Honeybee\FrameworkBinding\Silex\Controller;

use Honeybee\ServiceLocatorInterface;
use Silex\CallbackResolver;
use Silex\ServiceControllerResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;

class ControllerResolver extends ServiceControllerResolver
{
    protected $serviceLocator;

    public function __construct(
        ControllerResolverInterface $controllerResolver,
        CallbackResolver $callbackResolver,
        ServiceLocatorInterface $serviceLocator
    ) {
        parent::__construct($controllerResolver, $callbackResolver);

        $this->serviceLocator = $serviceLocator;
    }

    public function getController(Request $request)
    {
        $controller = $request->attributes->get('_controller', null);

        if (is_string($controller) || is_array($controller)) {
            $callable = $this->makeController($controller);
        } else {
            $callable = false;
        }

        if (!$callable) {
            $callable = parent::getController($request);
        }

        return $callable;
    }

    protected function makeController($controller)
    {
        if (is_string($controller)) {
            if (false === strpos($controller, '::')) {
                return false;
            }
            $controller = explode('::', $controller);
        }
        if (count($controller) !== 2) {
            return false;
        }
        list($controllerClass, $controllerMethod) = $controller;

        if (!class_exists($controllerClass)) {
            return false;
        }

        return [ $this->serviceLocator->make($controllerClass), $controllerMethod ];
    }
}
