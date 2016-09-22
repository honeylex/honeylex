<?php

namespace Honeybee\FrameworkBinding\Silex\Bootstrap;

use Silex\Application;

class ConsoleBootstrap extends Bootstrap
{
    public function __invoke(Application $app, array $settings)
    {
        parent::__invoke($app, $settings);

        return $app;
    }
}
