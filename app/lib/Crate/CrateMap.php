<?php

namespace Honeylex\Crate;

use Trellis\Common\Collection\MandatoryKeyInterface;
use Trellis\Common\Collection\TypedMap;

class CrateMap extends TypedMap implements MandatoryKeyInterface
{
    public function getItemImplementor()
    {
        return CrateInterface::CLASS;
    }
}
