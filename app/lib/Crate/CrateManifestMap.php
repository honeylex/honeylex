<?php

namespace Honeylex\Crate;

use Honeylex\Crate\CrateManifestInterface;
use Trellis\Common\Collection\MandatoryKeyInterface;
use Trellis\Common\Collection\TypedMap;

class CrateManifestMap extends TypedMap implements MandatoryKeyInterface
{
    public function getItemImplementor()
    {
        return CrateManifestInterface::CLASS;
    }
}
