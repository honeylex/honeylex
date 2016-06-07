<?php

// everything in here will be mounted below the prefix '/system_account/user'

use Foh\SystemAccount\User\Controller\HelloController;
use Foh\SystemAccount\User\Controller\IndexController;
use Foh\SystemAccount\User\Controller\ListController;
use Foh\SystemAccount\User\Controller\Task\ModifyController;

$routing->mount('/user', function ($routing) {
    $routing->get('/', [ IndexController::CLASS, 'read' ])
        ->bind('foh.system_account.user.index');

    $routing->get('/hello', [ HelloController::CLASS, 'read' ])
        ->bind('foh.system_account.user.hello');

    $routing->post('/list', [ ListController::CLASS, 'write' ]);
    $routing->get('/list', [ ListController::CLASS, 'read' ])
        ->bind('foh.system_account.user.list');

    $routing->get('/{identifier}/tasks/edit', [ ModifyController::CLASS, 'read' ])
        ->bind('foh.system_account.user.tasks.modify');

    $routing->post('/{identifier}/tasks/edit', [ ModifyController::CLASS, 'write' ]);
});
