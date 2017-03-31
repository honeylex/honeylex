<?php

namespace Honeylex\Console\Command\Resource;

use Honeybee\Common\Util\StringToolkit;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class RemoveResource extends ResourceCommand
{
    protected function configure()
    {
        $this
            ->setName('resource:rm')
            ->setDescription('Removes a specific resource from a crate.')
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
}
