<?php

namespace Honeylex\Renderer\Twig\Extension;

use Honeybee\Common\Util\StringToolkit;
use Honeylex\Config\ConfigProviderInterface;
use Twig_Extension;
use Twig_SimpleFilter;
use Twig_SimpleFunction;

class ProjectExtension extends Twig_Extension
{
    protected $configProvider;

    public function __construct(ConfigProviderInterface $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    public function getName()
    {
        return 'project';
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('config', [ $this, 'getConfig' ])
        ];
    }

    public function getFilters()
    {
        return [
            new Twig_SimpleFilter('snake', function ($string) {
                return StringToolkit::asSnakeCase($string);
            }),
            new Twig_SimpleFilter('camel', function ($string) {
                return StringToolkit::asCamelCase($string);
            })
        ];
    }

    public function getConfig($path, $default = null)
    {
        return $this->configProvider->getSetting($path, $default);
    }
}
