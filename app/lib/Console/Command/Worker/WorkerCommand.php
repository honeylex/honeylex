<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command\Worker;

use Honeybee\FrameworkBinding\Silex\Config\ConfigProviderInterface;
use Honeybee\FrameworkBinding\Silex\Console\Command\Command;
use Honeybee\Infrastructure\Job\JobServiceInterface;
use Honeybee\ServiceLocatorInterface;

abstract class WorkerCommand extends Command
{
    protected $serviceLocator;

    protected $jobService;

    public function __construct(
        ConfigProviderInterface $configProvider,
        ServiceLocatorInterface $serviceLocator,
        JobServiceInterface $jobService
    ) {
        parent::__construct($configProvider);

        $this->serviceLocator = $serviceLocator;
        $this->jobService = $jobService;
    }
}
