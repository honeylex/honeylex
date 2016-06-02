Honeylex App Skeleton
==============

Test integration of the honeybee cqrs+es lib with the silex app-framework.

What silex stuff is included?
---------------

The Silex Skeleton is configured with the following service providers:

* `UrlGeneratorServiceProvider`_ - Provides a service for generating URLs for
  named routes.

* `ValidatorServiceProvider`_ - Provides a service for validating data. It is
  most useful when used with the FormServiceProvider, but can also be used
  standalone.

* `ServiceControllerServiceProvider`_ - As your Silex application grows, you
  may wish to begin organizing your controllers in a more formal fashion.
  Silex can use controller classes out of the box, but with a bit of work,
  your controllers can be created as services, giving you the full power of
  dependency injection and lazy loading.

* `TwigServiceProvider`_ - Provides integration with the Twig template engine.

* `WebProfilerServiceProvider`_ - Enable the Symfony web debug toolbar and
  the Symfony profiler in your Silex application when developing.

* `MonologServiceProvider`_ - Enable logging in the development environment.

Read the `Providers`_ documentation for more details about Silex Service
Providers.


.. _Composer: http://getcomposer.org/
.. _Documentation: http://silex.sensiolabs.org/documentation
.. _UrlGeneratorServiceProvider: http://silex.sensiolabs.org/doc/providers/url_generator.html
.. _ValidatorServiceProvider: http://silex.sensiolabs.org/doc/providers/validator.html
.. _ServiceControllerServiceProvider: http://silex.sensiolabs.org/doc/providers/service_controller.html
.. _TwigServiceProvider: http://silex.sensiolabs.org/doc/providers/twig.html
.. _WebProfilerServiceProvider: http://github.com/silexphp/Silex-WebProfiler
.. _MonologServiceProvider: http://silex.sensiolabs.org/doc/providers/monolog.html
.. _Providers: http://silex.sensiolabs.org/doc/providers.html
