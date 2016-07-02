<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command\Resource;

use Honeybee\FrameworkBinding\Silex\Config\ConfigProviderInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Trellis\CodeGen\Console\GenerateCodeCommand;

class GenerateCode extends ResourceCommand
{
    protected $fileFinder;

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
            ->setName('hlx:res:code')
            ->setDescription('Scaffold entities off a specific schema-definition within a given crate.')
            ->addArgument(
                'crate',
                InputArgument::REQUIRED,
                'The prefix of the crate to generate the resource for.'
            )
            ->addArgument(
                'resource',
                InputArgument::REQUIRED,
                'The name of the resource to generate the code for.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cratePrefix = $input->getArgument('crate');
        $resourceName = $input->getArgument('resource');

        if (!$resourceName || !$cratePrefix) {
            $output->writeln('<error>You must specify at least a crate-prefix and resource-name.</error>');
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
