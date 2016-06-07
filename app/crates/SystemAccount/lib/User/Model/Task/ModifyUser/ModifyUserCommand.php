<?php

namespace Foh\SystemAccount\User\Model\Task\ModifyUser;

use Honeybee\Model\Task\ModifyAggregateRoot\ModifyAggregateRootCommand;

class ModifyUserCommand extends ModifyAggregateRootCommand
{
    public function getEventClass()
    {
        return UserModifiedEvent::CLASS;
    }
}
