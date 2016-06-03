<?php

// everything in here will be mounted below the prefix '/system_account/user'

use Honeybee\SystemAccount\User\Controller\UserDefaultController;

$routing->get('/', [ UserDefaultController::CLASS, 'indexAction' ])->bind('system_account.user.index');
$routing->get('/hello', [ UserDefaultController::CLASS, 'indexAction' ])->bind('system_account.user.hello');
