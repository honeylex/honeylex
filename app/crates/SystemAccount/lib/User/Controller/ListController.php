<?php

namespace Foh\SystemAccount\User\Controller;

use Foh\SystemAccount\User\HelloService;
use Foh\SystemAccount\User\Model\Aggregate\UserType;
use Foh\SystemAccount\User\Model\Task\CreateUser\CreateUserCommand;
use Honeybee\Infrastructure\Command\Bus\CommandBusInterface;
use Honeybee\Infrastructure\Template\TemplateRendererInterface;
use Honeybee\Model\Command\AggregateRootCommandBuilder;
use Shrink0r\Monatic\Success;

class ListController
{
    protected $userType;

    protected $templateRenderer;

    protected $commandBus;

    public function __construct(
        UserType $userType,
        TemplateRendererInterface $templateRenderer,
        CommandBusInterface $commandBus)
    {
        $this->userType = $userType;
        $this->templateRenderer = $templateRenderer;
        $this->commandBus = $commandBus;
    }

    public function write()
    {
        $createData = [
            'username' => 'shrink0r',
            'firstname' => 'Thorsten',
            'lastname' => 'Schmitt-Rink',
            'email' => 'schmittrink@gmail.com',
            'role' => 'administrator'
        ];

        $result = (new AggregateRootCommandBuilder($this->userType, CreateUserCommand::CLASS))
            ->withValues($createData)
            ->build();

        if ($result instanceof Success) {
            $this->commandBus->post($result->get());
            $message = 'Successfully created a new user, here is the list again ...';
        } else {
            $message = 'Failed to create user: '.var_export($result->get(), true);
        }

        return $this->templateRenderer->render('@SystemAccount/user/list.twig', [ 'message' => $message ]);
    }

    public function read()
    {
        return $this->templateRenderer->render('@SystemAccount/user/list.twig', [ 'message' => 'here is the user list ...' ]);
    }
}
