<?php

// everything in here will be mounted below the prefix '/system_account/user'

use Foh\SystemAccount\User\Controller\IndexController;
use Foh\SystemAccount\User\Controller\ListController;
use Foh\SystemAccount\User\Controller\Task\ModifyController;
use Foh\SystemAccount\User\Controller\Task\ProceedWorkflowController;

$routing->mount('/user', function ($routing) {
    $routing->get('/', [ IndexController::CLASS, 'read' ])
        ->bind('foh.system_account.user.index');

    $routing->post('/list', [ ListController::CLASS, 'write' ]);
    $routing->get('/list', [ ListController::CLASS, 'read' ])
        ->bind('foh.system_account.user.list');

    $routing->post('/{identifier}/tasks/edit', [ ModifyController::CLASS, 'write' ]);
    $routing->get('/{identifier}/tasks/edit', [ ModifyController::CLASS, 'read' ])
        ->bind('foh.system_account.user.tasks.modify');

    $routing->match('/{identifier}/tasks/proceed', [ ProceedWorkflowController::CLASS, 'write' ])
        ->bind('foh.system_account.user.tasks.proceed');
});
