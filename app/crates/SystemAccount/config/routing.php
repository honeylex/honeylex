<?php

// everything in here will be mounted below the prefix '/system_account'

use Honeybee\SystemAccount\User\Controller\UserDefaultController;

require __DIR__.'/User/routing.php';

$routing->get('/', function () use ($app) {
    return $app['twig']->render('@SystemAccount/index.twig');
});
