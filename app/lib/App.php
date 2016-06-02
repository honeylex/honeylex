<?php

namespace Honeybee\FrameworkBinding\Silex;

use Honeybee\ServiceLocatorInterface;
use Silex\Application;

class App extends Application
{
    public function __construct(ServiceLocatorInterface $serviceLocator, array $values = [])
    {
        parent::__construct($values);

        $this['service_locator'] = $serviceLocator;
    }
}
