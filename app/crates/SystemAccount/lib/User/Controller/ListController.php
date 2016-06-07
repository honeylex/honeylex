<?php

namespace Foh\SystemAccount\User\Controller;

use Foh\SystemAccount\User\HelloService;
use Foh\SystemAccount\User\Model\Aggregate\UserType;
use Foh\SystemAccount\User\Model\Task\CreateUser\CreateUserCommand;
use Honeybee\Infrastructure\Command\Bus\CommandBusInterface;
use Honeybee\Infrastructure\DataAccess\Query\CriteriaList;
use Honeybee\Infrastructure\DataAccess\Query\Query;
use Honeybee\Infrastructure\DataAccess\Query\QueryServiceMap;
use Honeybee\Infrastructure\Template\TemplateRendererInterface;
use Honeybee\Model\Command\AggregateRootCommandBuilder;
use Shrink0r\Monatic\Success;
use Silex\Application;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ListController
{
    protected $userType;

    protected $templateRenderer;

    protected $commandBus;

    protected $queryServiceMap;

    protected $app;

    public function __construct(
        UserType $userType,
        TemplateRendererInterface $templateRenderer,
        CommandBusInterface $commandBus,
        QueryServiceMap $queryServiceMap,
        Application $app
    ) {
        $this->userType = $userType;
        $this->templateRenderer = $templateRenderer;
        $this->commandBus = $commandBus;
        $this->queryServiceMap = $queryServiceMap;
        $this->app = $app;
    }

    public function read()
    {
        $form = $this->buildUserForm($this->app['form.factory']);
        $search = $this->fetchUserList();

        return $this->templateRenderer->render(
            '@SystemAccount/user/list.twig',
            [ 'form' => $form->createView(), 'user_list' => $search, 'status' => '' ]
        );
    }

    public function write(Request $request)
    {
        $form = $this->buildUserForm($this->app['form.factory']);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            return $this->templateRenderer->render(
                '@SystemAccount/user/list.twig',
                [ 'form' => $form->createView(), 'user_list' => $search, 'status' => 'Form validation error' ]
            );
        }

        $result = (new AggregateRootCommandBuilder($this->userType, CreateUserCommand::CLASS))
            ->withValues($form->getData())
            ->build();

        if ($result instanceof Success) {
            $this->commandBus->post($result->get());
            return $this->app->redirect('/index_dev.php/foh/system_account/user/list');
        }

        $status = 'Failed to create user: '.var_export($result->get(), true);
        return $this->templateRenderer->render(
            '@SystemAccount/user/list.twig',
            [ 'form' => $form->createView(), 'user_list' => $this->fetchUserList(), 'status' => $status ]
        );
    }

    protected function fetchUserList($offset = 0, $limit = 10)
    {
        $query = new Query(new CriteriaList, new CriteriaList, new CriteriaList, 0, $limit);

        return $this->queryServiceMap
            ->getItem($this->userType->getPrefix().'::query_service')
                ->find($query);
    }

    protected function buildUserForm(FormFactory $formFactory)
    {
        $data = [
            'username' => '',
            'firstname' => '',
            'lastname' => '',
            'email' => '',
            'role' => ''
        ];

        return $formFactory->createBuilder(FormType::CLASS, $data)
            ->add('username', TextType::CLASS, ['constraints' => [ new NotBlank, new Length([ 'min' => 5 ]) ]])
            ->add('email', TextType::CLASS, [ 'constraints' => new Email ])
            ->add('firstname')
            ->add('lastname')
            ->add('email')
            ->add('role', ChoiceType::CLASS, [
                'choices' => [ 'administrator' => 'administrator', 'user' => 'user' ],
                'constraints' => new Choice([ 'administrator', 'user' ]),
            ])
            ->getForm();
    }
}
