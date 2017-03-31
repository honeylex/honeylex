<?php

namespace Honeylex\Console\Command\Fixture;

use Honeybee\Infrastructure\Fixture\FixtureServiceInterface;
use Honeylex\Config\ConfigProviderInterface;
use Honeylex\Console\Command\Command;

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
