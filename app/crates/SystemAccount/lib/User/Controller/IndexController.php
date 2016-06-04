<?php

namespace Honeybee\SystemAccount\User\Controller;

use Honeybee\Infrastructure\DataAccess\DataAccessServiceInterface;
use Honeybee\Infrastructure\Template\TemplateRendererInterface;

class IndexController
{
    protected $templateRenderer;

    public function __construct(TemplateRendererInterface $templateRenderer, DataAccessServiceInterface $dbal)
    {
        $this->templateRenderer = $templateRenderer;
    }

    public function read()
    {
        return $this->templateRenderer->render('@SystemAccount/user/index.twig');
    }
}
