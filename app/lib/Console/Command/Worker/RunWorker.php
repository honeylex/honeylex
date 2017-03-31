<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command\Worker;

use Honeybee\Infrastructure\Config\ArrayConfig;
use Honeybee\Infrastructure\Job\Worker\Worker;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class RunWorker extends WorkerCommand
{
    protected function configure()
    {
        $this
            ->setName('worker:run')
            ->setDescription('Run an asynchronous job worker.')
            ->addArgument(
                'queue',
                InputArgument::OPTIONAL,
                'Name of the message queue from which to execute jobs.'
            );
    }

    protected function writeHeader(OutputInterface $output)
    {
        $output->writeln('');
        $output->writeln('Honeylex job worker');
        $output->writeln('-------------------');
        $output->writeln('');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$queueName = $input->getArgument('queue')) {
            $this->writeHeader($output);
            $queueName = $this->listQueues($input, $output);
        }

        $jobConfig = new ArrayConfig([ 'queue' => $queueName ]);

        $worker = $this->serviceLocator->make(Worker::CLASS, [ ':config' => $jobConfig ]);
        $worker->run();
    }

    protected function listQueues(InputInterface $input, OutputInterface $output)
    {
        $jobMap = $this->jobService->getJobMap();

        if (!count($jobMap)) {
            $output->writeln('<error>There are no jobs available.</error>');
            $output->writeln('');
            exit;
        }

        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            'Please select a queue: ',
            array_map(
                function ($job) {
                    return $job['settings']['queue'];
                },
                $jobMap->getValues()
            )
        );

        return $helper->ask($input, $output, $question);
    }
}
