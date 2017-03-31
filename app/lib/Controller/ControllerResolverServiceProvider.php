<?php

namespace Honeylex\Controller;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ControllerResolverServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app->extend('resolver', function ($resolver, $app) {
            return new ControllerResolver($resolver, $app['callback_resolver'], $app['honeybee.service_locator']);
        });
    }
}
