<?php

namespace Foh\SystemAccount\Controller;

use Honeybee\Infrastructure\Command\Bus\CommandBusInterface;
use Honeybee\Infrastructure\Template\TemplateRendererInterface;

class IndexController
{
    protected $templateRenderer;

    public function __construct(TemplateRendererInterface $templateRenderer, CommandBusInterface $cbus)
    {
        $this->templateRenderer = $templateRenderer;
    }

    public function read()
    {
        return $this->templateRenderer->render('@SystemAccount/index.twig', [ 'title' => __METHOD__ ]);
    }
}
