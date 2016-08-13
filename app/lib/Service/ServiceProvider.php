<?php

namespace Honeybee\FrameworkBinding\Silex\Service;

use Honeybee\ServiceProvisionerInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\EventListenerProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ServiceProvider implements ServiceProviderInterface, EventListenerProviderInterface
{
    protected $serviceProvisioner;

    public function __construct(ServiceProvisionerInterface $serviceProvisioner)
    {
        $this->serviceProvisioner = $serviceProvisioner;
    }

    public function register(Container $app)
    {
        $app['honeybee.service_locator'] = $this->serviceProvisioner->provision();
    }

    public function subscribe(Container $app, EventDispatcherInterface $dispatcher)
    {
        $this->serviceProvisioner->subscribe();
    }
}
