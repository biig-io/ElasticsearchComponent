<?php

namespace Biig\Component\Elasticsearch\Test\Integration\Symfony\DependencyInjection;


use Biig\Component\Elasticsearch\Integration\Symfony\DependencyInjection\ElasticsearchExtension;
use Elastica\Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class ElasticsearchExtensionTest extends TestCase
{
    public function testItAddElasticaClientToContainer()
    {
        $extension = new ElasticsearchExtension();

        $container = $this->createContainer();
        $extension->load([['mapping' => [], 'connections' => []]], $container);

        $this->assertNotEmpty($container->getDefinition(Client::class));
        $this->assertNull($container->compile());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testItFailOnWrongMappingFolder()
    {
        $extension = new ElasticsearchExtension();

        $container = $this->createContainer(['kernel.root_dir' => __DIR__]);
        $extension->load([[
            'connections' => [],
            'mapping' => [],
        ]], $container);

        $this->assertNull($container->compile());
    }

    private function createContainer(array $params = [])
    {
        return new ContainerBuilder(new ParameterBag(array_merge([
            'kernel.root_dir' => __DIR__ . '/../..',
        ], $params)));
    }
}
