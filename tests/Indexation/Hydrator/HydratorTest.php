<?php

namespace Biig\Component\Elasticsearch\Test\Indexation\Hydrator;

use Biig\Component\Elasticsearch\Indexation\Hydrator\Hydrator;
use Biig\Component\Elasticsearch\Indexation\TypeInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class HydratorTest extends TestCase
{
    public function testItInsertDataBasedOnGetIdMethod()
    {
        $type = $this->prophesize(TypeInterface::class);
        $type->getPaginator()->willReturn([
            [
                new DummyObject(1),
                new DummyObject(2),
                new DummyObject(3),
            ],
            [
                new DummyObject(4),
                new DummyObject(5),
            ],
        ]);
        $type->stageForInsert(['id' => 1], 1)->shouldBeCalled();
        $type->stageForInsert(['id' => 2], 2)->shouldBeCalled();
        $type->stageForInsert(['id' => 3], 3)->shouldBeCalled();
        $type->stageForInsert(['id' => 4], 4)->shouldBeCalled();
        $type->stageForInsert(['id' => 5], 5)->shouldBeCalled();
        $type->flush()->shouldBeCalledTimes(2);
        $type->getName()->willReturn('foo');

        $hydrator = new Hydrator($type->reveal(), new ObjectNormalizer());
        $hydrator();
    }

    public function testItInsertWithoutId()
    {
        $type = $this->prophesize(TypeInterface::class);
        $type->getPaginator()->willReturn([
            [
                new DummyObjectNoId('Hello'),
            ],
        ]);
        $type->stageForInsert(['content' => 'Hello'], null)->shouldBeCalled();
        $type->flush()->shouldBeCalledTimes(1);
        $type->getName()->willReturn('foo');

        $hydrator = new Hydrator($type->reveal(), new ObjectNormalizer());
        $hydrator();
    }
}

class DummyObject
{
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }
}

class DummyObjectNoId
{
    private $content;

    public function __construct($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }
}
