<?php

namespace Biig\Component\Elasticsearch\Test\Indexation\Hydrator;

use Biig\Component\Elasticsearch\Indexation\Hydrator\Hydrator;
use Biig\Component\Elasticsearch\Indexation\Hydrator\HydratorFactory;
use Biig\Component\Elasticsearch\Indexation\TypeInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class HydratorFactoryTest extends TestCase
{
    public function testItCreateHydrator()
    {
        $serializer = $this->prophesize(NormalizerInterface::class)->reveal();
        $type = $this->prophesize(TypeInterface::class)->reveal();
        $factory = new HydratorFactory($serializer);

        $hydrator = $factory->create($type);

        $this->assertInstanceOf(Hydrator::class, $hydrator);
    }
}
