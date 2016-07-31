# Honeylex

A cqrs plus es (cms) app boilerplate for php based on the integration of the [Honeybee][Honeybee] lib with the [silex][Documentation] framework.

[![Code Climate](https://codeclimate.com/github/honeylex/honeylex/badges/gpa.svg)](https://codeclimate.com/github/honeylex/honeylex)
[![Dependency Status](https://www.versioneye.com/user/projects/579b94f7aa78d500469f9701/badge.svg?style=flat)](https://www.versioneye.com/user/projects/579b94f7aa78d500469f9701)

## Installation

### Docker

You can have Honeylex running very quickly with [Docker][Docker]:

Install and run as follows:
```shell
git clone git@github.com:honeylex/honeylex.git your-project
cd your-project/var/docker
docker-compose up -d
docker exec -ti docker_elasticsearch_1 bin/plugin install analysis-icu
docker exec -ti docker_elasticsearch_1 bin/plugin install mobz/elasticsearch-head
docker-compose restart
# project installation
docker exec -ti docker_front_1 /var/www/bin/console hlx:project:install
docker exec -ti docker_front_1 /var/www/bin/console hlx:migrate:up
```

### Local

In order to get Honeylex running without virtualization you'll need to make sure that your machine meets the following requirements:

* [Composer][Composer]
* php >= 5.6
* [elasticsearch 2.x](https://www.elastic.co/downloads/elasticsearch)
* [couchdb 1.6.x](http://couchdb.apache.org)
* [rabbitmq](https://www.rabbitmq.com) - Only required if you want support for async background processing.

#### Install:

* Run: ```composer create-project -sdev honeylex/honeylex your-project```
* Install: ```cd your-project; bin/console hlx:project:install```
* Create a directory: ```/usr/local/honeylex.local/```
* In this directory create a file named ```rabbitmq.json``` with the following contents: ```{ "user":"name", "password":"secret", "host": "localhost", "port": 5672 }```
* Run: ```bin/console hlx:migrate:up```
* Run: ```composer run```, this will start a local webserver that hosts the app [here](http://localhost:8888/)


## Console

A full list of supported console commands for scaffolding crates and resources, managing migrations and more can be found by running:
```shell
bin/console
# or
docker exec -ti docker_front_1 /var/www/bin/console
```

### Registered silex service providers

The bootstrapped Silex app is configured with the following service providers:

* [AssetServiceProvider][AssetServiceProvider]
* [FormServiceProvider][FormServiceProvider]
* [MonologServiceProvider][MonologServiceProvider]
* [ServiceControllerServiceProvider][ServiceControllerServiceProvider]
* [TranslationServiceProvider][TranslationServiceProvider]
* [TwigServiceProvider][TwigServiceProvider]
* [UrlGeneratorServiceProvider][UrlGeneratorServiceProvider]
* [ValidatorServiceProvider][ValidatorServiceProvider]
* [WebProfilerServiceProvider][WebProfilerServiceProvider]
* [SwiftmailerServiceProvider][SwiftmailerServiceProvider]

Read the [Providers][Providers] documentation for more details about Silex Service Providers.

[AssetServiceProvider]: http://silex.sensiolabs.org/doc/providers/asset.html
[Composer]: http://getcomposer.org/
[Docker]: https://docs.docker.com/engine/installation/
[Documentation]: http://silex.sensiolabs.org/documentation
[FormServiceProvider]: http://silex.sensiolabs.org/doc/providers/form.html
[Honeybee]: http://github.com/honeybee/honeybee
[MonologServiceProvider]: http://silex.sensiolabs.org/doc/providers/monolog.html
[Providers]: http://silex.sensiolabs.org/doc/providers.html
[ServiceControllerServiceProvider]: http://silex.sensiolabs.org/doc/providers/service_controller.html
[SwiftmailerServiceProvider]: http://silex.sensiolabs.org/doc/providers/swiftmailer.html
[TranslationServiceProvider]: http://silex.sensiolabs.org/doc/providers/translation.html
[TwigServiceProvider]: http://silex.sensiolabs.org/doc/providers/twig.html
[UrlGeneratorServiceProvider]: http://silex.sensiolabs.org/doc/providers/url_generator.html
[ValidatorServiceProvider]: http://silex.sensiolabs.org/doc/providers/validator.html
[WebProfilerServiceProvider]: http://github.com/silexphp/Silex-WebProfiler
