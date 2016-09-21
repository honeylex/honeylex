<?php

namespace Honeybee\FrameworkBinding\Silex\Serializer;

use Honeybee\Projection\Projection;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProjectionNormalizer implements NormalizerInterface
{
    public function normalize($object, $format = null, array $context = [])
    {
        return $object->toArray();
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Projection;
    }
}
