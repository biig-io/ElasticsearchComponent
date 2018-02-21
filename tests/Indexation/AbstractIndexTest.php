<?php

namespace Biig\Component\Elasticsearch\Test\Indexation;

use App\Domain\Adp\Elasticsearch\IndexAdpLead;
use Biig\Component\Elasticsearch\Indexation\AbstractIndex;
use Elastica\Client;
use Elastica\Document;
use PHPUnit\Framework\TestCase;
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
     * @var IndexAdpLead
     */
    private $index;

    public function setUp()
    {
        $this->client = $this->prophesize(Client::class);
        $this->logger = $this->prophesize(LoggerInterface::class);

        $this->index = new FakeIndex($this->client->reveal(), $this->logger->reveal());
    }

    public function testItIsInsertDocument()
    {
        $document = new Document('1', ['data' => 'value'], 'bar', 'foo');
        $this->client->addDocuments([$document])->shouldBeCalled();

        $this->index->insert(['data' => 'value'], '1');
    }

    public function testItIsUpdateDocument()
    {
        $document = new Document('1', ['data' => 'value'], 'bar', 'foo');
        $this->client->updateDocuments([$document])->shouldBeCalled();

        $this->index->update(['data' => 'value'], '1');
    }
}

class FakeIndex extends AbstractIndex
{
    public function getName()
    {
        return 'foo';
    }

    public function getTypeName()
    {
        return 'bar';
    }
}
