<?php

namespace Honeybee\FrameworkBinding\Silex\Service\Provisioner;

use Auryn\Injector;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\ServiceDefinitionInterface;
use Pimple\Container;

interface ProvisionerInterface
{
    public function provision(
        Container $app,
        Injector $injector,
        ServiceDefinitionInterface $serviceDefinition,
        SettingsInterface $provisionerSettings
    );
}
