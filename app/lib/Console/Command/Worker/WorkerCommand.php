<?php

namespace Honeylex\Console\Command\Worker;

use Honeybee\Infrastructure\Job\JobServiceInterface;
use Honeybee\ServiceLocatorInterface;
use Honeylex\Config\ConfigProviderInterface;
use Honeylex\Console\Command\Command;

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
