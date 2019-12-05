<?php

namespace Biig\Component\Elasticsearch\Test\Indexation;

use Biig\Component\Elasticsearch\Exception\IndexationError;
use Biig\Component\Elasticsearch\Exception\NoElasticaTypeAvailable;
use Biig\Component\Elasticsearch\Indexation\AbstractIndex;
use Biig\Component\Elasticsearch\Indexation\AbstractType;
use Biig\Component\Elasticsearch\Indexation\Doctrine\SimplePaginator;
use Biig\Component\Elasticsearch\Indexation\Hydrator\Hydrator;
use Biig\Component\Elasticsearch\Indexation\Hydrator\HydratorFactory;
use Biig\Component\Elasticsearch\Indexation\IndexInterface;
use Biig\Component\Elasticsearch\Mapping\IndexBuilder;
use Elastica\Bulk\ResponseSet;
use Elastica\Client;
use Elastica\Document;
use Elastica\Exception\ElasticsearchException;
use Elastica\Exception\ResponseException;
use Elastica\Index;
use Elastica\Response;
use Elastica\SearchableInterface;
use Elastica\Type;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class AbstractTypeTest extends TestCase
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function setUp()
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
    }

    /**
     * @expectedException \Biig\Component\Elasticsearch\Exception\DocumentNotFoundException
     */
    public function testItAcceptTypes()
    {
        $type = $this->prophesize(Type::class);
        $response = $this->prophesize(Response::class);
        $exception = $this->prophesize(ResponseException::class);
        $exception->getResponse()->willReturn($response->reveal());
        $response->getFullError()->willReturn(['type' => 'document_missing_exception']);

        $fooType = new FooType($this->logger->reveal());
        $fooType->setType($type->reveal());

        $type->updateDocument(Argument::any())->willThrow($exception->reveal());

        $fooType->update(new \stdClass(), 'foo');
    }

    public function testItFlushes()
    {
        $type = $this->prophesize(Type::class);
        $index = $this->prophesize(Index::class);
        $responseSet = $this->prophesize(ResponseSet::class);

        $fooType = new FooType($this->logger->reveal());
        $fooType->setType($type->reveal());
        $fooType->stageForInsert(['object1'], 1);
        $fooType->stageForInsert(['object2'], 2);

        $responseSet->hasError()->willReturn(false);
        $type->addDocuments([
            new Document(1, ['object1']),
            new Document(2, ['object2'])
        ])->shouldBeCalled()->willReturn($responseSet);

        $type->getIndex()->willReturn($index);
        $index->refresh()->shouldBeCalled();

        $fooType->flush();

        $this->assertEquals([], $fooType->getStageForInsert());
    }

    public function testItThrowsExceptionIfNoType()
    {
        $fooType = new FooType($this->logger->reveal());

        $this->expectException(NoElasticaTypeAvailable::class);
        $fooType->flush();
    }

    public function testItThrowsExceptionOnIndexationError()
    {
        $type = $this->prophesize(Type::class);
        $responseSet = $this->prophesize(ResponseSet::class);

        $fooType = new FooType($this->logger->reveal());
        $fooType->setType($type->reveal());
        $fooType->stageForInsert(['object1'], 1);
        $fooType->stageForInsert(['object2'], 2);

        $responseSet->hasError()->willReturn(true);
        $type->addDocuments([
            new Document(1, ['object1']),
            new Document(2, ['object2'])
        ])->shouldBeCalled()->willReturn($responseSet);
        $responseSet->getError()->willReturn('error');

        $this->expectException(IndexationError::class);

        $fooType->flush();
    }

}

class FooType extends AbstractType
{
    /**
     * @var array
     */
    private $stagedForInsert = [];

    public function getPaginator(): SimplePaginator
    {
    }

    public function getName(): string
    {
        return 'bar';
    }

    public function getStageForInsert()
    {
        return $this->stagedForInsert;
    }
}
