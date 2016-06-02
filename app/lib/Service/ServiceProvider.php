<?php

namespace Honeybee\FrameworkBinding\Silex\Service;

use Honeybee\ServiceProvisionerInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
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
}
