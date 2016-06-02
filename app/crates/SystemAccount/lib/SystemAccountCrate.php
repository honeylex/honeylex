<?php

namespace Honeybee\SystemAccount;

use Honeybee\FrameworkBinding\Silex\Crate\CrateInterface;
use Honeybee\SystemAccount\User\Controller\UserControllerProvider;
use Honeybee\SystemAccount\User\Controller\UserDefaultController;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;

class SystemAccountCrate implements CrateInterface
{
    public function getPrefix()
    {
        return 'system_account';
    }

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
