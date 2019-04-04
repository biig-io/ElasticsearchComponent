<?php

namespace Biig\Component\Elasticsearch\Test\Mapping;

use Biig\Component\Elasticsearch\Mapping\IndexBuilder;
use Elastica\Client;
use Elastica\Index;
use PHPUnit\Framework\TestCase;

class IndexBuilderTest extends TestCase
{
    /**
     * @expectedException \Exception
     */
    public function testItChecksMappingFolders()
    {
        $client = $this->prophesize(Client::class)->reveal();
        $builder = new IndexBuilder($client, [__DIR__ . '/foo']);
    }

    public function testItCreateMappingBasedOnMappingFilesForOneIndex()
    {
        $client = $this->prophesize(Client::class);
        $index = $this->prophesize(Index::class);
        $index->create(
            json_decode(
                file_get_contents(__DIR__ . '/../config/elasticsearch/leads.json'),
                true
            ),
            true
        )->shouldBeCalled();
        $client->getIndex('leads')->shouldBeCalled()->willReturn($index->reveal());

        $builder = new IndexBuilder($client->reveal(), [__DIR__ . '/../config/elasticsearch']);
        $builder->create('leads');
    }

    public function testItCreateMappingBasedOnMappingFilesForMultipleIndex()
    {
        $client = $this->prophesize(Client::class);
        $index = $this->prophesize(Index::class);
        $index->create(
            json_decode(
                file_get_contents(__DIR__ . '/../config/elasticsearch/leads.json'),
                true
            ),
            true
        )->shouldBeCalled();
        $index->create(
            json_decode(
                file_get_contents(__DIR__ . '/../config/elasticsearch/beers.json'),
                true
            ),
            true
        )->shouldBeCalled();
        $client->getIndex('leads')->shouldBeCalled()->willReturn($index->reveal());
        $client->getIndex('beers')->shouldBeCalled()->willReturn($index->reveal());

        $builder = new IndexBuilder($client->reveal(), [__DIR__ . '/../config/elasticsearch']);
        $builder->create();
    }

    public function testItCanAddASuffixForOneIndex()
    {
        $client = $this->prophesize(Client::class);
        $index = $this->prophesize(Index::class);
        $index->create(
            json_decode(
                file_get_contents(__DIR__ . '/../config/elasticsearch/leads.json'),
                true
            ),
            true
        )->shouldBeCalled();
        $client->getIndex('leads_v150')->shouldBeCalled()->willReturn($index->reveal());

        $builder = new IndexBuilder($client->reveal(), [__DIR__ . '/../config/elasticsearch']);
        $builder->setSuffix('_v150');
        $builder->create('leads');
    }

    public function testItCanAddASuffixForMultipleIndex()
    {
        $client = $this->prophesize(Client::class);
        $index = $this->prophesize(Index::class);
        $index->create(
            json_decode(
                file_get_contents(__DIR__ . '/../config/elasticsearch/leads.json'),
                true
            ),
            true
        )->shouldBeCalled();
        $index->create(
            json_decode(
                file_get_contents(__DIR__ . '/../config/elasticsearch/beers.json'),
                true
            ),
            true
        )->shouldBeCalled();
        $client->getIndex('leads_v150')->shouldBeCalled()->willReturn($index->reveal());
        $client->getIndex('beers_v150')->shouldBeCalled()->willReturn($index->reveal());

        $builder = new IndexBuilder($client->reveal(), [__DIR__ . '/../config/elasticsearch']);
        $builder->setSuffix('_v150');
        $builder->create();
    }
}
