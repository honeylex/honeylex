<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command\Resource;

use Honeybee\Common\Util\StringToolkit;
use Honeybee\FrameworkBinding\Silex\Console\Scaffold\SkeletonGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class GenerateCommand extends ResourceCommand
{
    protected function configure()
    {
        $this
            ->setName('resource:cmd')
            ->setDescription('Scaffold a resource command.')
            ->addArgument(
                'crate',
                InputArgument::OPTIONAL,
                'The prefix of the crate to make the command in.'
            )
            ->addArgument(
                'resource',
                InputArgument::OPTIONAL,
                'The name of the resource to make the command for.'
            )
            ->addArgument(
                'cmd',
                InputArgument::OPTIONAL,
                'The name of the command.'
            )
            ->addArgument(
                'event',
                InputArgument::OPTIONAL,
                'The name of the event.'
            )
            ->addOption(
                'location',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'Optional override of the locations that will be searched for skeletons.'
            );
    }

    protected function writeHeader(OutputInterface $output)
    {
        $output->writeln('');
        $output->writeln('Honeylex command scaffolding');
        $output->writeln('-----------------------------');
        $output->writeln('');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        if (!$cratePrefix = $input->getArgument('crate')) {
            $this->writeHeader($output);
            $cratePrefix = $this->listCrates($input, $output);
        }

        if (!$resourceName = $input->getArgument('resource')) {
            $resourceName = $this->listResources($input, $output, $cratePrefix);
        }

        if (!$resourceName || !$cratePrefix) {
            $output->writeln('<error>You must specify at least a crate and resource.</error>');
            return false;
        }

        if (!$commandName = $input->getArgument('cmd')) {
            $question = new Question('Please provide a command name: ');
            $commandName = $helper->ask($input, $output, $question);
        }

        if (!$eventName = $input->getArgument('event')) {
            $question = new Question('Please provide an event name: ');
            $eventName = $helper->ask($input, $output, $question);
        }

        if (!$commandName || !$eventName) {
            $output->writeln('<error>You must specify at least a command and event name.</error>');
            return false;
        }

        $crate = $this->configProvider->getCrateMap()->getItem($cratePrefix);
        $parentPath = $this->configProvider->getProjectDir().'/vendor/honeybee/honeybee/src/Model/Task';
        $foundParents = $this->fileFinder->create()->directories()->in($parentPath)->depth(0);
        foreach ($foundParents as $fileInfo) {
            $parentCommands[] = $fileInfo->getFilename();
        }
        $question = new ChoiceQuestion('Please select a parent type: ', $parentCommands);
        $selectedParent = $helper->ask($input, $output, $question);
        $selectedParentPath = $parentPath.'/'.$selectedParent;
        $events = $this->fileFinder->create()->files()->name('*Event.php')->in($selectedParentPath)->depth(0);
        $eventParent = pathinfo(current(iterator_to_array($events, true)), PATHINFO_FILENAME);
        $skeletonLocations = $this->getSkeletonLocations($input->getOption('location') ?: null);

        $output->writeln('Target crate: '.$crate->getVendor().'/'.$crate->getName());
        $output->writeln('Resource prefix: '.$cratePrefix.'.'.StringToolkit::asSnakeCase($resourceName));
        $output->writeln('Resource namespace: '.$crate->getNamespace().'\\'.$resourceName);
        $output->writeln('Directories: ');
        $output->writeln('  - '.$crate->getRootDir().'/lib/'.$resourceName.'/Model/Task/'.$commandName);
        $output->writeln('Skeleton locations:');
        foreach ($skeletonLocations as $skeletonLocation) {
            $output->writeln('  - '.$skeletonLocation);
        }

        $data = [
            'crate' => [
                'vendor' => $crate->getVendor(),
                'name' => $crate->getName()
            ],
            'resource' => [
                'name' => $resourceName
            ],
            'parent' => [
                'namespace' => 'Honeybee\\Model\\Task\\'.$selectedParent,
                'name' =>  basename($selectedParent),
                'event' => $eventParent
            ],
            'command' => [
                'name' => $commandName
            ],
            'event' => [
                'name' => $eventName
            ]
        ];

        $skeletonGenerator = new SkeletonGenerator(
            $this->configProvider,
            'command',
            $skeletonLocations,
            $crate->getRootDir(),
            $data
        );
        $skeletonGenerator->generate();

        // @todo auto add command to command bus configuration
        $configPath = $crate->getRootDir().'/config/'.$resourceName.'/command_bus.yml';
        $output->writeln("<error>Now add command to bus configuration at $configPath.</error>");
    }
}
