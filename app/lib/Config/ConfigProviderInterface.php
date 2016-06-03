<?php

namespace Honeybee\FrameworkBinding\Silex\Config;

interface ConfigProviderInterface
{
    public function getVersion();

    public function provide($config);

    public function getCrateMap();
}
