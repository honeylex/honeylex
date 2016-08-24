<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command\Worker;

use Honeybee\Infrastructure\Job\JobServiceInterface;
use Symfony\Component\Console\Command\Command;
use Honeybee\ServiceLocatorInterface;

abstract class WorkerCommand extends Command
{
    protected $serviceLocator;

    protected $jobService;

    public function __construct(
        ServiceLocatorInterface $serviceLocator,
        JobServiceInterface $jobService
    ) {
        $this->serviceLocator = $serviceLocator;
        $this->jobService = $jobService;

        parent::__construct();
    }
}
