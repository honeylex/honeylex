<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command\Event;

use Honeybee\Model\Aggregate\AggregateRootTypeInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReplayEvents extends EventCommand
{
    protected function configure()
    {
        $this
            ->setName('hlx:event:replay')
            ->setDescription('Replay aggregate root type domain events on the specified channel.')
            ->addArgument(
                'type',
                InputArgument::REQUIRED,
                'The name of the aggregate root type to migrate.'
            )
            ->addArgument(
                'channel',
                InputArgument::REQUIRED,
                'The channel on which to replay domain events.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->replay($output, $input->getArgument('type'), $input->getArgument('channel'));
    }

    protected function replay(OutputInterface $output, $type, $channel)
    {
        $aggregateRootType = $this->aggregateRootTypeMap->getItem($type);

        $distributedEvents = [];
        foreach ($this->getChronologicEventIterator($aggregateRootType) as $event) {
            $event_type = $event->getType();
            if (!isset($distributedEvents[$event_type])) {
                $distributedEvents[$event_type] = 0;
            }
            $distributedEvents[$event_type]++;

            $this->eventBus->distribute($channel, $event);
        }

        foreach ($distributedEvents as $event_type => $count) {
            $output->writeln('event: '.$event_type);
            $output->writeln(sprintf('status: replayed %d event%s', $count,  $count > 1 ? 's' : ''));
        }
    }

    protected function getChronologicEventIterator(AggregateRootTypeInterface $aggregateRootType)
    {
        $reader_key = sprintf('%s::domain_event::event_source::reader', $aggregateRootType->getPrefix());
        return $this->dataAccessService->getStorageReader($reader_key);
    }
}
