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
use Elastica\Exception\ElasticsearchException;
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
