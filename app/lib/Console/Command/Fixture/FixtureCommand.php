<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command\Fixture;

use Honeybee\Infrastructure\Fixture\FixtureServiceInterface;
use Symfony\Component\Console\Command\Command;

abstract class FixtureCommand extends Command
{
    protected $fixtureService;

    public function __construct(FixtureServiceInterface $fixtureService)
    {
        $this->fixtureService = $fixtureService;

        parent::__construct();
    }
}
