<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command\Event;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListChannels extends EventCommand
{
    protected function configure()
    {
        $this
            ->setName('hlx:event:channels')
            ->setDescription('List available event bus channels.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->eventBus->getChannels()->getKeys() as $channel) {
            $output->writeln($channel);
        }
    }
}
