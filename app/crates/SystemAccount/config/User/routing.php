<?php

// everything in here will be mounted below the prefix '/system_account/user'

use Honeybee\SystemAccount\User\Controller\HelloController;
use Honeybee\SystemAccount\User\Controller\IndexController;

$routing->mount('/user', function ($routing) {
    $routing->get('/', [ IndexController::CLASS, 'read' ])->bind('system_account.user.index');
    $routing->get('/hello', [ HelloController::CLASS, 'read' ])->bind('system_account.user.hello');
});
