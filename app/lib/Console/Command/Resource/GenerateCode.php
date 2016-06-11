<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command\Resource;

use Honeybee\Common\Util\StringToolkit;
use Honeybee\FrameworkBinding\Silex\Config\ConfigProviderInterface;
use Honeybee\FrameworkBinding\Silex\Console\Scafold\SkeletonGenerator;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Trellis\CodeGen\Console\GenerateCodeCommand;
use Trellis\CodeGen\Parser\Schema\EntityTypeSchemaXmlParser;

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
            ->setDescription('Scafold entities off a specific schema-definition within a given crate.')
            ->addArgument(
                'crate',
                InputArgument::OPTIONAL,
                "The prefix of the crate to generate the resource for."
            )
            ->addArgument(
                'resource',
                null,
                InputArgument::REQUIRED,
                "The name of the resource to generate the code for."
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cratePrefix = $input->getArgument('crate');
        $resourceName = $input->getArgument('resource');
        $crate = $this->configProvider->getCrateMap()->getItem($cratePrefix);
        if (!$resourceName || !$cratePrefix || !$crate) {
            $output->writeln('<error>You must specify at least a crate-prefix and resource-name.</error>');
            return false;
        }

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
