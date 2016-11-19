<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command\Event;

use Honeybee\FrameworkBinding\Silex\Config\ConfigProviderInterface;
use Honeybee\FrameworkBinding\Silex\Console\Command\Command;
use Honeybee\Infrastructure\DataAccess\DataAccessServiceInterface;
use Honeybee\Infrastructure\Event\Bus\EventBusInterface;
use Honeybee\Model\Aggregate\AggregateRootTypeMap;

abstract class EventCommand extends Command
{
    protected $aggregateRootTypeMap;

    protected $eventBus;

    protected $dataAccessService;

    public function __construct(
        ConfigProviderInterface $configProvider,
        AggregateRootTypeMap $aggregateRootTypeMap,
        EventBusInterface $eventBus,
        DataAccessServiceInterface $dataAccessService
    ) {
        parent::__construct($configProvider);

        $this->aggregateRootTypeMap = $aggregateRootTypeMap;
        $this->eventBus = $eventBus;
        $this->dataAccessService = $dataAccessService;
    }
}
