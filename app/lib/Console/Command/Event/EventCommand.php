<?php

namespace Honeylex\Console\Command\Event;

use Honeybee\Infrastructure\DataAccess\DataAccessServiceInterface;
use Honeybee\Infrastructure\Event\Bus\EventBusInterface;
use Honeybee\Model\Aggregate\AggregateRootTypeMap;
use Honeylex\Config\ConfigProviderInterface;
use Honeylex\Console\Command\Command;

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
