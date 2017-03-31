<?php

namespace Honeylex\Serializer;

use Honeybee\Projection\ProjectionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProjectionNormalizer implements NormalizerInterface
{
    public function normalize($object, $format = null, array $context = [])
    {
        return $object->toArray();
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProjectionInterface;
    }
}
