<?php

// everything in here will be mounted below the prefix '/system_account/user'

use Foh\SystemAccount\User\Controller\HelloController;
use Foh\SystemAccount\User\Controller\IndexController;

$routing->mount('/user', function ($routing) {
    $routing->get('/', [ IndexController::CLASS, 'read' ])->bind('foh.system_account.user.index');
    $routing->get('/hello', [ HelloController::CLASS, 'read' ])->bind('foh.system_account.user.hello');
});
