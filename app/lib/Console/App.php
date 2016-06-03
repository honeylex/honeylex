<?php

namespace Honeybee\FrameworkBinding\Silex\Console;

use Honeybee\FrameworkBinding\Silex\Config\ConfigProviderInterface;
use Silex\Application as SilexApp;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputOption;

class App extends Application
{
    protected $configProvider;

    public static function getLogo()
    {
        return <<<ASCII

/\ \                                     /\_ \
\ \ \___     ___     ___      __   __  __\//\ \      __   __  _
 \ \  _ `\  / __`\ /' _ `\  /'__`\/\ \/\ \ \ \ \   /'__`\/\ \/'\
  \ \ \ \ \/\ \L\ \/\ \/\ \/\  __/\ \ \_\ \ \_\ \_/\  __/\/>  </
   \ \_\ \_\ \____/\ \_\ \_\ \____\\/`____ \/\____\ \____\/\_/\_\
    \/_/\/_/\/___/  \/_/\/_/\/____/ `/___/> \/____/\/____/\//\/_/
                                       /\___/
                                       \/__/
ASCII;
    }

    public function __construct(SilexApp $app, array $appCommands, ConfigProviderInterface $configProvider)
    {
        $this->configProvider = $configProvider;

        parent::__construct('honeylex', $configProvider->getVersion());

        $this->getDefinition()->addOption(
            new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', 'dev')
        );
        foreach (array_map([ $app['honeybee.service_locator'], 'createEntity'], $appCommands) as $command) {
            $this->add($command);
        }
        $this->setDispatcher($app['dispatcher']);
    }

    public function getHelp()
    {
        return self::getLogo() . parent::getHelp();
    }
}
