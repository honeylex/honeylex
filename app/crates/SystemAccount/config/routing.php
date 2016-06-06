<?php

// everything in here will be mounted below the prefix '/system_account'

use Foh\SystemAccount\Controller\IndexController;

require __DIR__.'/User/routing.php';

$routing->get('/', [ IndexController::CLASS, 'read' ])->bind($this->getPrefix().'.index');
