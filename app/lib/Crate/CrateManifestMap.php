<?php

namespace Honeybee\FrameworkBinding\Silex\Crate;

use Honeybee\FrameworkBinding\Silex\Crate\CrateManifestInterface;
use Trellis\Common\Collection\MandatoryKeyInterface;
use Trellis\Common\Collection\TypedMap;

class CrateManifestMap extends TypedMap implements MandatoryKeyInterface
{
    public function getItemImplementor()
    {
        return CrateManifestInterface::CLASS;
    }
}
