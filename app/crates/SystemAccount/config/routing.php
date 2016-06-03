<?php

// everything in here will be mounted below the prefix '/system_account'

use Honeybee\SystemAccount\User\Controller\UserDefaultController;

$controllers->mount('/user', function ($routing) {
    require __DIR__.'/User/routing.php';
});

$routing->get('/', [ UserDefaultController::CLASS, 'indexAction' ])->bind('system_account.index');
