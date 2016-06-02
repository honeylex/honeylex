<?php

namespace Honeybee\FrameworkBinding\Silex\Provisioner;

use Auryn\Injector;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\ServiceDefinitionInterface;

interface ProvisionerInterface
{
    public function provision(
        Injector $injector,
        ServiceDefinitionInterface $service_definition,
        SettingsInterface $provisioner_settings
    );
}
