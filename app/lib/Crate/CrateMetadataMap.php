<?php

namespace Honeybee\FrameworkBinding\Silex\Crate;

use Honeybee\FrameworkBinding\Silex\Crate\CrateMetadataInterface;
use Trellis\Common\Collection\MandatoryKeyInterface;
use Trellis\Common\Collection\TypedMap;

class CrateMetadataMap extends TypedMap implements MandatoryKeyInterface
{
    public function getItemImplementor()
    {
        return CrateMetadataInterface::CLASS;
    }
}
