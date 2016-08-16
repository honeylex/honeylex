<?php

namespace Honeybee\FrameworkBinding\Silex\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class HttpLocaleListener implements EventSubscriberInterface
{
    private $defaultLocale;

    private $fallbackLocales;

    public function __construct($defaultLocale = 'en', array $fallbackLocales = [ 'en' ])
    {
        $this->defaultLocale = $defaultLocale;
        $this->fallbackLocales = $fallbackLocales;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $acceptedLocales = array_reduce(
            explode(',', $request->headers->get('Accept-Language')),
            function ($res, $el) {
                list($l, $q) = array_merge(explode(';q=', $el), [1]);
                $res[str_replace('-', '_', $l)] = (float) $q;
                return $res;
            },
            []
        );
        asort($acceptedLocales);

        $acceptedLocale = array_reduce(
            array_keys($acceptedLocales),
            function ($default, $prefLocale) {
                return in_array($prefLocale, $this->fallbackLocales) ? $prefLocale : $default;
            },
            $this->defaultLocale
        );

        $request->setLocale($acceptedLocale);
    }

    public static function getSubscribedEvents()
    {
        return [
            // must be registered before Session or User locale listeners
            KernelEvents::REQUEST => [[ 'onKernelRequest', 15 ]],
        ];
    }
}
