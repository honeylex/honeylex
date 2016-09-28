<?php

namespace Honeybee\FrameworkBinding\Silex\Renderer\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig_Extension;
use Twig_SimpleFunction;

class LocaliseRouteExtension extends Twig_Extension
{
    private $urlGenerator;

    private $requestStack;

    public function __construct(RequestStack $requestStack, UrlGeneratorInterface $urlGenerator)
    {
        $this->requestStack = $requestStack;
        $this->urlGenerator = $urlGenerator;
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('localise_route', [ $this, 'localiseRoute' ])
        ];
    }

    public function localiseRoute($locale)
    {
        $request = $this->requestStack->getMasterRequest();

        // Merge query parameters and route attributes
        $attributes = array_merge(
            $request->query->all(),
            $request->attributes->get('_route_params', [])
        );

        $attributes['_locale'] = $locale;

        return $this->urlGenerator->generate($request->attributes->get('_route', 'home'), $attributes);
    }

    public function getName()
    {
        return 'localise_route';
    }
}
