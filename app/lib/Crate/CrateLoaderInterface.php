<?php

namespace Honeybee\FrameworkBinding\Silex\Crate;

use Honeybee\FrameworkBinding\Silex\Crate\CrateMetadataMap;
use Silex\Application;

interface CrateLoaderInterface
{
    public function loadCrates(Application $app, CrateMetadataMap $crateMetadataMap);
}
