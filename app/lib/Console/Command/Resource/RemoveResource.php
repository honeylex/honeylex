<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command\Resource;

use Honeybee\Common\Util\StringToolkit;
use Honeybee\FrameworkBinding\Silex\Config\ConfigProviderInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Trellis\CodeGen\Parser\Schema\EntityTypeSchemaXmlParser;

class RemoveResource extends ResourceCommand
{
    public function __construct(
        ConfigProviderInterface $configProvider,
        Finder $fileFinder
    ) {
        parent::__construct($configProvider);
        $this->fileFinder = $fileFinder;
    }

    protected function configure()
    {
        $this
            ->setName('hlx:res:rm')
            ->setDescription('Removes a specific resource from the given crate.')
            ->addArgument(
                'crate',
                InputArgument::OPTIONAL,
                'The prefix of the crate to remove the resource from.'
            )
            ->addArgument(
                'resource',
                InputArgument::OPTIONAL,
                'The name of the resource to remove.'
            );
    }

    protected function writeHeader(OutputInterface $output)
    {
        $output->writeln('');
        $output->writeln('Honeylex resource removal');
        $output->writeln('-------------------------');
        $output->writeln('');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
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

        if (!$this->confirm($input, $output)) {
            $output->writeln('Resource removal aborted.');
            return false;
        }

        $crate = $this->configProvider->getCrateMap()->getItem($cratePrefix);

        $crateDir = $crate->getRootDir();
        $resourceDirectories = [
            $crate->getRootDir().'/config/'.$resourceName,
            $crate->getRootDir().'/lib/'.$resourceName,
            $crate->getRootDir().'/templates/'.StringToolkit::asSnakeCase($resourceName)
        ];
        // @todo tricky: find and remove proper migration directories
        foreach ($resourceDirectories as $resourceDirectory) {
            $output->writeln('<info>Removing resource dir '.$resourceDirectory.'</info>');
            (new Filesystem)->remove($resourceDirectory);
        }
        // @todo also tricky: find and run proper migrations
    }

    protected function listCrates(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion('Please select a crate: ', $this->configProvider->getCrateMap()->getKeys());
        return $helper->ask($input, $output, $question);
    }

    protected function listResources(InputInterface $input, OutputInterface $output, $cratePrefix)
    {
        $helper = $this->getHelper('question');
        $crate = $this->configProvider->getCrateMap()->getItem($cratePrefix);
        $finder = clone $this->fileFinder;
        $foundSchemas = $finder->in($crate->getRootDir())->name('aggregate_root.xml');
        $resource_names = [];
        foreach (iterator_to_array($foundSchemas, true) as $fileInfo) {
           $entitySchema = (new EntityTypeSchemaXmlParser)->parse($fileInfo->getPathname());
           $typeDefinition = $entitySchema->getEntityTypeDefinition();
           $resource_names[] = $typeDefinition->getName();
        }
        $question = new ChoiceQuestion('Please select a resource: ', $resource_names);
        return $helper->ask($input, $output, $question);
    }
}
