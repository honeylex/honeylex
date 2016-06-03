<?php

namespace Honeybee\SystemAccount\Controller;

use Honeybee\Infrastructure\Template\TemplateRendererInterface;

class IndexController
{
    protected $templateRenderer;

    public function __construct(TemplateRendererInterface $templateRenderer)
    {
        $this->templateRenderer = $templateRenderer;
    }

    public function read()
    {
        return $this->templateRenderer->render('@SystemAccount/index.twig');
    }
}
