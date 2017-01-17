<?php

namespace Honeybee\FrameworkBinding\Silex\Bootstrap;

use Silex\Application;

class ConsoleBootstrap extends Bootstrap
{
    public function __invoke(Application $app, array $settings)
    {
        parent::__invoke($app, $settings);

        // Set host from environment for URL generation
        if ($serverHost = getenv('SERVER_HOST')) {
            $app['request_context']->setHost($serverHost);
        }

        return $app;
    }
}
