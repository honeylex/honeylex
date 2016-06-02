<?php

namespace Honeybee\FrameworkBinding\Silex;

use Auryn\Injector;
use Auryn\StandardReflector;
use Honeybee\FrameworkBinding\Silex\ServiceProvisioner;
use Honeybee\ServiceDefinitionMap;
use Honeybee\ServiceLocatorInterface;
use Silex\Application;

class App extends Application
{
    public function __construct(ServiceDefinitionMap $serviceDefinitions, array $values = [])
    {
        parent::__construct($values);

        $injector = new Injector(new StandardReflector);
        $serviceProvisioner = new ServiceProvisioner($this, $injector, $serviceDefinitions);
        $this['honeybee.service_locator'] = $serviceProvisioner->provision();
    }
}
