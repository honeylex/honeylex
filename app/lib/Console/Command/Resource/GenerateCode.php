<?php

namespace Honeylex\Console\Command\Resource;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Trellis\CodeGen\Console\GenerateCodeCommand;

class GenerateCode extends ResourceCommand
{
    protected function configure()
    {
        $this
            ->setName('resource:gen')
            ->setDescription('Scaffold entities from a resource schema within a crate.')
            ->addArgument(
                'crate',
                InputArgument::OPTIONAL,
                'The prefix of the crate to generate the resource for.'
            )
            ->addArgument(
                'resource',
                InputArgument::OPTIONAL,
                'The name of the resource to generate the code for.'
            );
    }

    protected function writeHeader(OutputInterface $output)
    {
        $output->writeln('');
        $output->writeln('Honeylex resource code generation');
        $output->writeln('---------------------------------');
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

        $crate = $this->configProvider->getCrateMap()->getItem($cratePrefix);

        $configDir = $crate->getRootDir().'/config/'.$resourceName;
        $arBasePath = $configDir.'/entity_schema/aggregate_root';
        $arInput = new ArrayInput(
            [
                'action' => 'gen+dep',
                '--schema' => $arBasePath.'.xml',
                '--config' => $arBasePath.'.ini'
            ]
        );
        $projectionBasePath = $configDir.'/entity_schema/projection/standard';
        $projectionInput = new ArrayInput(
            [
                'action' => 'gen+dep',
                '--schema' => $projectionBasePath.'.xml',
                '--config' => $projectionBasePath.'.ini'
            ]
        );

        $output->writeln(
            'Generating code for '.$crate->getVendor().'/'.$crate->getName().'/'.$resourceName.' resource:'
        );

        $output->writeln('- '.$arBasePath.'.xml');
        (new GenerateCodeCommand)->run($arInput, $output);

        $output->writeln('- '.$projectionBasePath.'.xml');
        (new GenerateCodeCommand)->run($projectionInput, $output);
    }
}
