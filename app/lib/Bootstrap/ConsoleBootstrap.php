<?php

namespace Honeybee\FrameworkBinding\Silex\Bootstrap;

use Silex\Application;

class ConsoleBootstrap extends Bootstrap
{
    public function __invoke(Application $app, array $settings)
    {
        parent::__invoke($app, $settings);

        // Update request context from environment for URL generation
        if ($hostName = getenv('HOST_NAME')) {
            $app['request_context']->setHost($hostName);
        }

        if ($hostScheme = getenv('HOST_SCHEME')) {
            $app['request_context']->setScheme($hostScheme);
        }

        if ($hostHttpPort = getenv('HOST_HTTP_PORT')) {
            $app['request_context']->setHttpPort($hostHttpPort);
        }

        if ($hostHttpsPort = getenv('HOST_HTTPS_PORT')) {
            $app['request_context']->setHttpsPort($hostHttpsPort);
        }

        return $app;
    }
}
