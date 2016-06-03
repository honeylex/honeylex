<?php

namespace Honeybee\SystemAccount;

use Honeybee\FrameworkBinding\Silex\Crate\Crate;
use Silex\Application;

class SystemAccountCrate extends Crate
{
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];
        $routing = $app;

        require $this->getConfigDir() . '/routing.php';

        return $controllers;
    }
}
