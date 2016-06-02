<?php

namespace Honeybee\SystemAccount;

use Honeybee\FrameworkBinding\Silex\Crate\Crate;
use Honeybee\SystemAccount\User\Controller\UserDefaultController;
use Silex\Application;

class SystemAccountCrate extends Crate
{
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->mount('/user', function ($routing) {
            $routing->get('/', [ UserDefaultController::CLASS, 'indexAction' ])
                ->bind('user.index');
            return $routing;
        });

        return $controllers;
    }
}
