<?php

namespace Honeybee\SystemAccount\User\Controller;

use Honeybee\Infrastructure\Template\TemplateRendererInterface;
use Honeybee\SystemAccount\User\HelloService;

class UserDefaultController
{
    protected $templateRenderer;

    protected $helloService;

    public function __construct(TemplateRendererInterface $templateRenderer, HelloService $helloService)
    {
        $this->templateRenderer = $templateRenderer;
        $this->helloService = $helloService;
    }

    public function index()
    {
        return $this->templateRenderer->render('@SystemAccount/user/index.twig');
    }

    public function hello()
    {
        return $this->templateRenderer->render('@SystemAccount/user/hello.twig', [ 'message' => $this->helloService->greet() ]);
    }
}
