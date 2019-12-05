<?php

namespace Biig\Component\Elasticsearch\Test\Indexation;

use Biig\Component\Elasticsearch\Exception\NoElasticaTypeAvailable;
use Biig\Component\Elasticsearch\Indexation\AbstractType;
use Biig\Component\Elasticsearch\Indexation\Doctrine\SimplePaginator;
use Elastica\Bulk\ResponseSet;
use Elastica\Document;
use Elastica\Exception\ResponseException;
use Elastica\Index;
use Elastica\Response;
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

        $documents = [
            new Document(1, ['object1']),
            new Document(2, ['object2']),
        ];

        $responseSet->hasError()->willReturn(false);
        $type->addDocuments($documents)->shouldBeCalledOnce()->willReturn($responseSet);

        $type->getIndex()->willReturn($index);
        $index->refresh()->shouldBeCalled();

        $fooType->flush();
        $fooType->flush();
    }

    public function testItDoesNotAddDocumentIfNoneStaged()
    {
        $type = $this->prophesize(Type::class);
        $index = $this->prophesize(Index::class);

        $fooType = new FooType($this->logger->reveal());
        $fooType->setType($type->reveal());

        $type->addDocuments()->shouldNotBeCalled();

        $type->getIndex()->willReturn($index);
        $index->refresh()->shouldBeCalled();

        $fooType->flush();
    }

    public function testItThrowsExceptionIfNoType()
    {
        $fooType = new FooType($this->logger->reveal());

        $this->expectException(NoElasticaTypeAvailable::class);
        $fooType->flush();
    }

    public function testItLogsErrorOnIndexationError()
    {
        $type = $this->prophesize(Type::class);
        $index = $this->prophesize(Index::class);
        $responseSet = $this->prophesize(ResponseSet::class);

        $fooType = new FooType($this->logger->reveal());
        $fooType->setType($type->reveal());
        $fooType->stageForInsert(['object1'], 1);
        $fooType->stageForInsert(['object2'], 2);

        $responseSet->hasError()->willReturn(true);
        $type->addDocuments([
            new Document(1, ['object1']),
            new Document(2, ['object2']),
        ])->shouldBeCalled()->willReturn($responseSet);
        $responseSet->getError()->willReturn('error');

        $this->logger->error('error')->shouldBeCalled();

        $type->getIndex()->willReturn($index);
        $index->refresh()->shouldBeCalled();

        $fooType->flush();
    }

}

class FooType extends AbstractType
{
    public function getPaginator(): SimplePaginator
    {
    }

    public function getName(): string
    {
        return 'bar';
    }
}
