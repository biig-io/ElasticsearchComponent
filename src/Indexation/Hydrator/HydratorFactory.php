<?php

namespace Biig\Component\Elasticsearch\Indexation\Hydrator;

use Biig\Component\Elasticsearch\Indexation\TypeInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class HydratorFactory
{
    /**
     * @var NormalizerInterface
     */
    private $serializer;

    public function __construct(NormalizerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function create(TypeInterface $type)
    {
        return new Hydrator($type, $this->serializer);
    }
}
