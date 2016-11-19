<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command\Fixture;

use Honeybee\FrameworkBinding\Silex\Config\ConfigProviderInterface;
use Honeybee\FrameworkBinding\Silex\Console\Command\Command;
use Honeybee\Infrastructure\Fixture\FixtureServiceInterface;

abstract class FixtureCommand extends Command
{
    protected $fixtureService;

    public function __construct(
        ConfigProviderInterface $configProvider,
        FixtureServiceInterface $fixtureService
    ) {
        parent::__construct($configProvider);

        $this->fixtureService = $fixtureService;
    }
}
