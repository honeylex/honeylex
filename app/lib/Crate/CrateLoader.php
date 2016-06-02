<?php

namespace Honeybee\FrameworkBinding\Silex\Crate;

use Silex\Application;

class CrateLoader implements CrateLoaderInterface
{
    public function loadCrates(Application $app, CrateMetadataMap $crateMetadataMap)
    {
        $crateMap = new CrateMap;
        foreach ($crateMetadataMap as $crateMetadata) {
            $crate = $this->load($crateMetadata);
            $crateMap->setItem($crateMetadata->getPrefix(), $crate);
            $app->mount($crateMetadata->getPrefix(), $crate);
        }

        return $crateMap;
    }

    protected function load(CrateMetadataInterface $crateMetadata)
    {
        $crateClass = $crateMetadata->getClass();

        return new $crateClass;
    }
}
