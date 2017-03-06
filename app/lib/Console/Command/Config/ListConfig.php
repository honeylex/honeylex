<?php

namespace Honeybee\FrameworkBinding\Silex\Console\Command\Config;

use Honeybee\FrameworkBinding\Silex\Console\Command\Command;
use Honeybee\Infrastructure\Config\Settings;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListConfig extends Command
{
    protected function configure()
    {
        $this
            ->setName('config:ls')
            ->setDescription('List configuration settings.')
            ->addArgument(
                'path',
                InputArgument::OPTIONAL,
                'Filter values for a given path'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $settings = new Settings;
        $handlerConfigs = $this->configProvider->getHandlerConfigs();
        $crateMap = $this->configProvider->getCrateMap();

        if ($path = $input->getArgument('path')) {
            $pathParts = explode('.', $path);
            $key = array_shift($pathParts);
            if ($handlerConfigs->has($key.'.yml')) {
                $settings = $this->configProvider->provide($key.'.yml');
                $settings = is_array($settings) ? $settings : $settings->toArray();
                // @todo support path searching
                if ($subPath = implode('.', $pathParts)) {
                    $settings = array_key_exists($subPath, $settings) ? $settings[$subPath] : null;
                }
                $settings = $settings ? Settings::createFromArray([ $path => $settings ]) : new Settings;
            } elseif ($this->configProvider->hasSetting($path)) {
                $settings = Settings::createFromArray([ $path => $this->configProvider->getSetting($path) ]);
            } elseif ($crateMap->hasKey($path)) {
                $settings = Settings::createFromArray([ $path => $this->configProvider->getCrateSettings($path) ]);
            }
        } else {
            foreach ($handlerConfigs->getKeys() as $configFile) {
                $settings = Settings::createFromArray(
                    array_merge(
                        $settings->toArray(),
                        [ pathinfo($configFile, PATHINFO_FILENAME) => $this->configProvider->provide($configFile) ]
                    )
                );
            }

            foreach($crateMap as $crate) {
                $settings = Settings::createFromArray(
                    array_merge(
                        $settings->toArray(),
                        [ $crate->getPrefix() => $this->configProvider->getCrateSettings($crate->getPrefix())->toArray() ]
                    )
                );
            }
        }

        // Render values
        $this->renderValues($output, $settings->toArray());
    }

    private function renderValues(OutputInterface $output, array $settings, $indent = 0)
    {
        foreach($settings as $key => $value) {
            if (is_scalar($value) || is_null($value)) {
                switch (true) {
                    case is_bool($value):
                        $value = $value === true ? "true" : "false";
                        break;
                    case is_null($value):
                        $value = "null";
                        break;
                    case $value === "":
                        $value = '""';
                        break;
                }
                $output->writeln(str_repeat(' ', $indent*2)."<info>$key</info>: $value");
            } elseif (empty($value)) {
                $output->writeln(str_repeat(' ', $indent*2)."<info>$key</info>: []");
            } else {
                $output->writeln(str_repeat(' ', $indent*2)."<info>$key</info>: [");
                $this->renderValues($output, $value, ++$indent);
                $output->writeln(str_repeat(' ', --$indent*2)."]");
            }
        }
    }
}
