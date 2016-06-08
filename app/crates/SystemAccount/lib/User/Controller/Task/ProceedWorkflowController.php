<?php

namespace Foh\SystemAccount\User\Controller\Task;

use Foh\SystemAccount\User\Model\Aggregate\UserType;
use Foh\SystemAccount\User\Model\Task\ProceedUserWorkflow\ProceedUserWorkflowCommand;
use Honeybee\Infrastructure\Command\Bus\CommandBusInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class ProceedWorkflowController
{
    protected $userType;

    protected $commandBus;

    public function __construct(UserType $userType, CommandBusInterface $commandBus)
    {
        $this->userType = $userType;
        $this->commandBus = $commandBus;
    }

    public function write(Request $request, Application $app)
    {
        if ($request->getMethod() !== 'POST') {
            return 'Method not allowed.';
        }
        $proceedCommand = new ProceedUserWorkflowCommand([
            'aggregate_root_type' => $this->userType->getPrefix(),
            'aggregate_root_identifier' => $request->get('identifier'),
            'known_revision' => (int)$request->get('revision'),
            'current_state_name' => $request->get('from'),
            'event_name' => $request->get('via')
        ]);

        $this->commandBus->post($proceedCommand);

        return $app->redirect($app['url_generator']->generate('foh.system_account.user.list'));
    }
}
