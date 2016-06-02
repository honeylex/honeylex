<?php

namespace Honeybee\SystemAccount\User\Controller;

use Honeybee\Infrastructure\Template\TemplateRendererInterface;

class UserDefaultController
{
    protected $template_renderer;

    public function __construct(TemplateRendererInterface $template_renderer)
    {
        $this->template_renderer = $template_renderer;
    }

    public function indexAction()
    {
        return $this->template_renderer->render('user_index.twig', []);
    }
}
