<?php

namespace Honeylex\Console\Command\Event;

use Honeybee\Model\Aggregate\AggregateRootTypeInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class ReplayEvents extends EventCommand
{
    protected function configure()
    {
        $this
            ->setName('event:replay')
            ->setDescription('Replay aggregate root domain events on a channel.')
            ->addArgument(
                'type',
                InputArgument::OPTIONAL,
                'The name of the aggregate root type to migrate.'
            )
            ->addArgument(
                'channel',
                InputArgument::OPTIONAL,
                'The channel on which to replay domain events.'
            );
    }

    protected function writeHeader(OutputInterface $output)
    {
        $output->writeln('');
        $output->writeln('Honeylex event replay');
        $output->writeln('---------------------');
        $output->writeln('');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$type = $input->getArgument('type')) {
            $this->writeHeader($output);
            $type = $this->listTypes($input, $output);
        }

        if (!$channel = $input->getArgument('channel')) {
            $channel = $this->listChannels($input, $output);
        }

        if (!$type || !$channel) {
            $output->writeln('<error>You must specify at least a type and channel.</error>');
            return false;
        }

        $this->replay($output, $type, $channel);
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
            $output->writeln(sprintf('status: replayed %d event%s', $count, $count > 1 ? 's' : ''));
        }
    }

    protected function getChronologicEventIterator(AggregateRootTypeInterface $aggregateRootType)
    {
        $reader_key = sprintf('%s::domain_event::event_source::reader', $aggregateRootType->getPrefix());
        return $this->dataAccessService->getStorageReader($reader_key);
    }

    protected function listTypes(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $types = $this->aggregateRootTypeMap->getKeys();
        $question = new ChoiceQuestion('Please select a type: ', $types);
        return $helper->ask($input, $output, $question);
    }

    protected function listChannels(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $channels = $this->eventBus->getChannels()->getKeys();
        $question = new ChoiceQuestion('Please select a channel: ', $channels);
        return $helper->ask($input, $output, $question);
    }
}
