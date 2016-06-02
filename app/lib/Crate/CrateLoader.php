<?php

namespace Honeybee\FrameworkBinding\Silex\Crate;

use Silex\Application;

class CrateLoader implements CrateLoaderInterface
{
    public function loadCrates(Application $app, CrateManifestMap $crateManifestMap)
    {
        $crateMap = new CrateMap;
        foreach ($crateManifestMap as $crateManifest) {
            $crate = $this->load($crateManifest);
            $crateMap->setItem($crateManifest->getPrefix(), $crate);
            $app->mount($crateManifest->getPrefix(), $crate);
        }

        return $crateMap;
    }

    protected function load(CrateManifestInterface $crateManifest)
    {
        $crateClass = $crateManifest->getClass();

        return new $crateClass($crateManifest);
    }
}
