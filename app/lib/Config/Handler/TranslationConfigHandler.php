<?php

namespace Honeybee\FrameworkBinding\Silex\Config\Handler;

use Honeybee\Common\Error\ConfigError;

class TranslationConfigHandler extends ArrayConfigHandler
{
    protected function handleConfigFile($configFile)
    {
        preg_match('#^.+-(?<locale>\w+).yml$#', $configFile, $matches);

        if (!isset($matches['locale'])) {
            throw new ConfigError(
                'Translation filename does not have a valid locale. Filename format '.
                'should be "translation-{locale}.yml".'
            );
        }

        return [ $matches['locale'] => $this->parse($configFile) ];
    }

    protected function mergeConfigs(array $out, array $in)
    {
        return array_merge($out, $in);
    }
}
