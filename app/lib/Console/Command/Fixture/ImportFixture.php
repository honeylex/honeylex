<?php

namespace Honeylex\Console\Command\Fixture;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class ImportFixture extends FixtureCommand
{
    protected function configure()
    {
        $this
            ->setName('fixture:import')
            ->setDescription('Import fixtures from a target.')
            ->addArgument(
                'target',
                InputArgument::OPTIONAL,
                'Name of the fixture target to import.'
            )
            ->addArgument(
                'fixture',
                InputArgument::OPTIONAL,
                'The fixture version to import from.'
            );
    }

    protected function writeHeader(OutputInterface $output)
    {
        $output->writeln('');
        $output->writeln('Honeylex fixture import');
        $output->writeln('-----------------------');
        $output->writeln('');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$fixtureTargetName = $input->getArgument('target')) {
            $this->writeHeader($output);
            $fixtureTargetName = $this->listFixtureTargets($input, $output);
        }

        if (!$fixtureName = $input->getArgument('fixture')) {
            $fixtureName = $this->listFixtures($input, $output, $fixtureTargetName);
        }

        if (!$fixtureTargetName || !$fixtureName) {
            $output->writeln('<error>You must specify a fixture target and fixture.</error>');
            return false;
        }

        $this->fixtureService->import($fixtureTargetName, $fixtureName);

        $output->writeln('Successfully imported fixtures.');
    }

    protected function listFixtureTargets(InputInterface $input, OutputInterface $output)
    {
        $fixtureTargetMap = $this->fixtureService->getFixtureTargetMap();

        if (!count($fixtureTargetMap)) {
            $output->writeln('<error>There are no fixture targets available.</error>');
            $output->writeln('');
            exit;
        }

        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion('Please select a fixture target: ', $fixtureTargetMap->getKeys());

        return $helper->ask($input, $output, $question);
    }

    protected function listFixtures(
        InputInterface $input,
        OutputInterface $output,
        $fixtureTargetName
    ) {
        $fixtureTargetMap = $this->fixtureService->getFixtureTargetMap();

        if (!$fixtureTargetMap->hasKey($fixtureTargetName)) {
            $output->writeln('<error>The given fixture target does not exist.</error>');
            $output->writeln('');
            exit;
        }
        $fixtureTarget = $fixtureTargetMap->getItem($fixtureTargetName);
        $fixtureList = $fixtureTarget->getFixtureList();

        if (!count($fixtureList)) {
            $output->writeln('<error>There are no fixtures available for this target.</error>');
            $output->writeln('');
            exit;
        }

        foreach ($fixtureList as $fixture) {
            $choices[] = sprintf('%s:%s', $fixture->getVersion(), $fixture->getName());
        }

        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion('Please select a fixture: ', $choices);

        return $helper->ask($input, $output, $question);
    }
}
