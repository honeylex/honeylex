<?php

namespace Foh\SystemAccount\User\Controller;

use Foh\SystemAccount\User\Model\Aggregate\UserType;
use Foh\SystemAccount\User\Model\Task\CreateUser\CreateUserCommand;
use Honeybee\Infrastructure\Command\Bus\CommandBusInterface;
use Honeybee\Infrastructure\DataAccess\Query\CriteriaList;
use Honeybee\Infrastructure\DataAccess\Query\Query;
use Honeybee\Infrastructure\DataAccess\Query\QueryServiceMap;
use Honeybee\Infrastructure\DataAccess\Query\SearchCriteria;
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
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ListController
{
    protected $userType;

    protected $templateRenderer;

    protected $commandBus;

    protected $queryServiceMap;

    public function __construct(
        UserType $userType,
        TemplateRendererInterface $templateRenderer,
        CommandBusInterface $commandBus,
        QueryServiceMap $queryServiceMap
    ) {
        $this->userType = $userType;
        $this->templateRenderer = $templateRenderer;
        $this->commandBus = $commandBus;
        $this->queryServiceMap = $queryServiceMap;
    }

    public function read(Request $request, Application $app)
    {
        $form = $this->buildUserForm($app['form.factory']);
        $query = $request->query->get('q', '');
        $errors = $app['validator']->validate($query, new Length([ 'max' => 100 ]));
        if (count($errors) > 0) {
            $query = '';
        }
        $page = $request->query->get('page', 1);
        $errors = $app['validator']->validate($page, new GreaterThan([ 'value' => 0 ]));
        if (count($errors) > 0) {
            $page = 1;
        }
        $limit = $request->query->get('limit', 10);
        $errors = $app['validator']->validate($limit, new GreaterThan([ 'value' => 0 ]));
        if (count($errors) > 0) {
            $limit = 10;
        }
        $search = $this->fetchUserList($query, $page, $limit);

        $pager = [
            'total' => ceil($search->getTotalCount() / $limit),
            'current' => $page,
            'next_url' => false,
            'prev_url' => false
        ];
        if (($page + 1) * $limit <= $search->getTotalCount()) {
            $pager['next_url'] = $app['url_generator']->generate(
                'foh.system_account.user.list',
                [ 'page' => $page + 1, 'limit' => $limit, 'q' => $query ]
            );
        }
        if (($page - 1) / $limit > 0) {
            $pager['prev_url'] = $app['url_generator']->generate(
                'foh.system_account.user.list',
                [ 'page' => $page - 1, 'limit' => $limit, 'q' => $query ]
            );
        }

        return $this->templateRenderer->render(
            '@SystemAccount/user/list.twig',
            [
                'form' => $form->createView(),
                'user_list' => $search,
                'pager' => $pager,
                'status' => '',
                'q' => $query
            ]
        );
    }

    public function write(Request $request, Application $app)
    {
        $form = $this->buildUserForm($app['form.factory']);
        $form->handleRequest($request);

        if (!$form->isValid()) {
            return $this->templateRenderer->render(
                '@SystemAccount/user/list.twig',
                [ 'form' => $form->createView(), 'user_list' => $this->fetchUserList('', 1, 10), 'status' => 'Form validation error' ]
            );
        }

        $result = (new AggregateRootCommandBuilder($this->userType, CreateUserCommand::CLASS))
            ->withValues($form->getData())
            ->build();

        if ($result instanceof Success) {
            $this->commandBus->post($result->get());
            return $app->redirect($request->getRequestUri());
        }

        $status = 'Failed to create user: '.var_export($result->get(), true);
        return $this->templateRenderer->render(
            '@SystemAccount/user/list.twig',
            [ 'form' => $form->createView(), 'user_list' => $this->fetchUserList('', 1, 10), 'status' => $status ]
        );
    }

    protected function fetchUserList($searchTerm, $page, $limit)
    {
        $searchCriteria = new CriteriaList;
        if (!empty($searchTerm)) {
            $searchCriteria->addItem(new SearchCriteria($searchTerm));
        }
        $query = new Query($searchCriteria, new CriteriaList, new CriteriaList, ($page - 1) * $limit, $limit);

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
