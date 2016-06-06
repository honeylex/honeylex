<?php

namespace Honeybee\FrameworkBinding\Silex\Twig;

use Honeybee\Common\Util\StringToolkit;
use Twig_Extension;
use Twig_SimpleFilter;

class TwigExtension extends Twig_Extension
{
    public function getName()
    {
        return 'project';
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
}