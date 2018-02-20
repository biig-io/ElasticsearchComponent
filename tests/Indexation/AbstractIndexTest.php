<?php

namespace Biig\Component\Elasticsearch\Test\Indexation;

use Biig\Component\Elasticsearch\Indexation\AbstractIndex;
use Biig\Component\Elasticsearch\Indexation\AbstractType;
use Biig\Component\Elasticsearch\Indexation\Doctrine\SimplePaginator;
use Biig\Component\Elasticsearch\Indexation\Hydrator\Hydrator;
use Biig\Component\Elasticsearch\Indexation\Hydrator\HydratorFactory;
use Biig\Component\Elasticsearch\Indexation\IndexInterface;
use Biig\Component\Elasticsearch\Mapping\IndexBuilder;
use Elastica\Client;
use Elastica\Index;
use Elastica\Type;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class AbstractIndexTest extends TestCase
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var AbstractType
     */
    private $index;

    /**
     * @var IndexBuilder
     */
    private $builder;

    /**
     * @var HydratorFactory
     */
    private $hydratorFactory;

    public function setUp()
    {
        $this->client = $this->prophesize(Client::class);
        $index = $this->prophesize(Index::class);
        $type = $this->prophesize(Type::class);
        $index->getType(Argument::any())->willReturn($type->reveal());
        $this->client->getIndex(Argument::any())->willReturn($index->reveal());
        $this->builder = $this->prophesize(IndexBuilder::class);
        $this->hydratorFactory = $this->prophesize(HydratorFactory::class);
        $this->logger = $this->prophesize(LoggerInterface::class);
    }

    public function testItAcceptTypes()
    {
        $this->instantiateIndex();

        $this->index->addType(new DummyType());
        $this->assertInstanceOf(AbstractType::class, $this->index->getType('bar'));
    }

    public function testItCreateIndex()
    {
        $this->builder->create('foo')->shouldBeCalled();
        $this->instantiateIndex();

        $this->index->create();
    }

    public function testItInsertAndUpdateUsingTheType()
    {
        $this->instantiateIndex();
        $type = $this->prophesize(AbstractType::class);
        $type->getName()->willReturn('bar');
        $type->setType(Argument::type(Type::class))->shouldBeCalled();
        $type->insert(['content'], null)->shouldBeCalled();
        $type->update(['content'], 'id00')->shouldBeCalled();

        $this->index->addType($type->reveal());

        $this->index->insert(['content'], 'bar');
        $this->index->update(['content'], 'bar', 'id00');
    }

    public function testItRetrieveHydrator()
    {
        $hydrator = $this->prophesize(Hydrator::class);
        $this->hydratorFactory->create(Argument::type(AbstractType::class))->willReturn($hydrator)->shouldBeCalled();
        $this->instantiateIndex();

        $this->index->addType(new DummyType());
        $this->index->getHydrator('bar');
    }

    private function instantiateIndex()
    {
        $this->index = new FakeIndex($this->client->reveal(), $this->builder->reveal(), $this->hydratorFactory->reveal(), $this->logger->reveal());
    }
}

class FakeIndex extends AbstractIndex
{
    public function __construct(Client $client, IndexBuilder $builder, HydratorFactory $hydratorFactory, LoggerInterface $logger = null, $foo = null)
    {
        parent::__construct($foo ?? 'foo', $client, $builder, $hydratorFactory, $logger);
    }

    protected function getInstance(string $name, Client $client, IndexBuilder $builder, HydratorFactory $hydratorFactory, LoggerInterface $logger): IndexInterface
    {
        return new self($client, $builder, $hydratorFactory, $logger, $name);
    }
}

class DummyType extends AbstractType
{
    public function getPaginator(): SimplePaginator
    {
    }

    public function getName(): string
    {
        return 'bar';
    }
}
