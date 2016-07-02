<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command\Event;

use Honeybee\Infrastructure\DataAccess\DataAccessServiceInterface;
use Honeybee\Infrastructure\Event\Bus\EventBusInterface;
use Honeybee\Model\Aggregate\AggregateRootTypeMap;
use Symfony\Component\Console\Command\Command;

abstract class EventCommand extends Command
{
    protected $aggregateRootTypeMap;

    protected $eventBus;

    protected $dataAccessService;

    public function __construct(
        AggregateRootTypeMap $aggregateRootTypeMap,
        EventBusInterface $eventBus,
        DataAccessServiceInterface $dataAccessService
    ) {
        $this->aggregateRootTypeMap = $aggregateRootTypeMap;
        $this->eventBus = $eventBus;
        $this->dataAccessService = $dataAccessService;

        parent::__construct();
    }
}
