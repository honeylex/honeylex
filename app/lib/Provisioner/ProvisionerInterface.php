<?php

namespace Honeybee\FrameworkBinding\Silex\Provisioner;

use Auryn\Injector;
use Honeybee\FrameworkBinding\Silex\App;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\ServiceDefinitionInterface;

interface ProvisionerInterface
{
    public function provision(
        App $app,
        Injector $injector,
        ServiceDefinitionInterface $serviceDefinition,
        SettingsInterface $provisionerSettings
    );
}
