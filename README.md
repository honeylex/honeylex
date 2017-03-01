# Honeylex

Project template for building rapidly scalable applications based on the integration of the [Honeybee][Honeybee] CQRS & ES framework with the [Silex][Silex] micro framework.

[![Code Climate](https://codeclimate.com/github/honeylex/honeylex/badges/gpa.svg)](https://codeclimate.com/github/honeylex/honeylex)
[![Dependency Status](https://www.versioneye.com/user/projects/579b94f7aa78d500469f9701/badge.svg?style=flat)](https://www.versioneye.com/user/projects/579b94f7aa78d500469f9701)

####Alternative Honeybee Integrations
 - [Honeylex-CMF](https://github.com/honeylex/honeylex-cmf) (Honeylex + CMS tooling)
 - [Honeyquip](https://github.com/honeyquip/honeyquip) (Honeybee + [Equip](https://github.com/equip/framework))
 - [Honeygavi](https://github.com/honeybee/honeybee-agavi-cmf-project) (Honeybee + [Agavi](https://github.com/agavi/agavi))

## Installation

### Docker

You can have Honeylex running very quickly with [Docker][Docker] and [Composer][Composer].
> If you do not already have Docker, first install it then [create a machine](https://docs.docker.com/machine/get-started/) with [Virtualbox](https://www.virtualbox.org/) if required.

Bring up the Honeylex project on Docker as follows:
```shell
git clone git@github.com:honeylex/honeylex.git your-project
cd your-project
composer install --ignore-platform-reqs
# don't forget to connect your shell with `eval $(docker-machine env default)`
composer docker:up
```

Now you can run commands to setup the project:
```shell
composer console:run hlx:project:configure
composer console:run hlx:migrate:up
```
**Once containers are running your project will be ready and provisioned!**
>Run `docker-machine ip default` to find the IP (typically http://192.168.99.100)
> - Secure site https://192.168.99.100 (untrusted certs in dev mode)
> - Elasticsearch at http://192.168.99.100:9200
> - CouchDB admin at http://192.168.99.100:5984/_utils
> - RabbitMQ admin at http://192.168.99.100:15672

You can configure various environment files in the ```var/environment``` folder of your host machine. The `.env` and `var/docker/docker-composer.yml` files also contain additional global project environment configuration.

The following docker commands are available via `composer` from your host machine:
```shell
composer docker:up    # bring up the containers without building
composer docker:down  # stops and removes the project containers
composer docker:start # start previously stopped containers
composer docker:stop  # stop/suspend the docker containers
```

##Console
Honeylex comes with a number of convenient tools to help project setup and maintenance. A complete list of commands can be found by running:
```shell
composer console:run  # alias of docker-compose run --rm php_cli ./bin/console
```

A useful set of commands are provided for managing the following system features:
 - Configuration
 - Crates (portable code context bundles)
 - Resources (entities such as aggregate roots & projections)
 - Migrations
 - Fixtures
 - Workers (long running asynchronous background processes)
 - Events (managing the event store)
 - Routing

## Registered Silex service providers

The bootstrapped Silex app is configured with the following service providers:

* [AssetServiceProvider][AssetServiceProvider]
* [FormServiceProvider][FormServiceProvider]
* [LocaleServiceProvider][LocaleServiceProvider]
* [MonologServiceProvider][MonologServiceProvider]
* [SessionServiceProvider][SessionServiceProvider]
* [SerializerServiceProvider][SerializerServiceProvider]
* [ServiceControllerServiceProvider][ServiceControllerServiceProvider]
* [SwiftmailerServiceProvider][SwiftmailerServiceProvider]
* [TranslationServiceProvider][TranslationServiceProvider]
* [TwigServiceProvider][TwigServiceProvider]
* [UrlGeneratorServiceProvider][UrlGeneratorServiceProvider]
* [ValidatorServiceProvider][ValidatorServiceProvider]
* [WebProfilerServiceProvider][WebProfilerServiceProvider]

Read the [Providers][Providers] documentation for more details about Silex Service Providers.

## Questions?

Join us in building awesome scalable applications or ask questions here:
 - IRC [freenode #honeybee](http://webchat.freenode.net?randomnick=1&channels=%23honeybee&uio=d4)
 - Gitter [honeybee #Lobby](https://gitter.im/honeybee/Lobby)
 - Slack [honeybee-cmf #development](https://honeybee-cmf.slack.com/messages/development)

[AssetServiceProvider]: http://silex.sensiolabs.org/doc/master/providers/asset.html
[Composer]: http://getcomposer.org/
[Docker]: https://docs.docker.com/engine/installation/
[FormServiceProvider]: http://silex.sensiolabs.org/doc/master/providers/form.html
[Honeybee]: http://github.com/honeybee/honeybee
[LocaleServiceProvider]: http://silex.sensiolabs.org/doc/master/providers/locale.html
[MonologServiceProvider]: http://silex.sensiolabs.org/doc/master/providers/monolog.html
[Providers]: http://silex.sensiolabs.org/doc/master/providers.html
[ServiceControllerServiceProvider]: http://silex.sensiolabs.org/doc/master/providers/service_controller.html
[Silex]: http://silex.sensiolabs.org/doc/master/
[SessionServiceProvider]: http://silex.sensiolabs.org/doc/master/providers/session.html
[SerializerServiceProvider]: http://silex.sensiolabs.org/doc/master/providers/serializer.html
[SwiftmailerServiceProvider]: http://silex.sensiolabs.org/doc/master/providers/swiftmailer.html
[TranslationServiceProvider]: http://silex.sensiolabs.org/doc/master/providers/translation.html
[TwigServiceProvider]: http://silex.sensiolabs.org/doc/master/providers/twig.html
[UrlGeneratorServiceProvider]: http://silex.sensiolabs.org/doc/providers/url_generator.html
[ValidatorServiceProvider]: http://silex.sensiolabs.org/doc/master/providers/validator.html
[WebProfilerServiceProvider]: http://github.com/silexphp/Silex-WebProfiler
