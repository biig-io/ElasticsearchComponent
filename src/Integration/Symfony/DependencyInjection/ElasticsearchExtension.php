<?php

namespace Biig\Component\Elasticsearch\Integration\Symfony\DependencyInjection;

use Biig\Component\Elasticsearch\Indexation\Hydrator\HydratorFactory;
use Biig\Component\Elasticsearch\Indexation\IndexInterface;
use Biig\Component\Elasticsearch\Mapping\IndexBuilder;
use Elastica\Client;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

class ElasticsearchExtension extends Extension
{
    const INDEX_TAG = 'biig_elasticsearch.index';

    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->configureElasticaClient($container, $config['connections']);
        $this->setMappingFolders($container, $config['mapping']);

        $container->getDefinition(HydratorFactory::class)->setArgument(0, new Reference($config['serializer']));
        $container->registerForAutoconfiguration(IndexInterface::class)->addTag(self::INDEX_TAG);
    }

    private function configureElasticaClient(ContainerBuilder $container, array $config)
    {
        $args = [
            '$config' => [
                'servers' => $config,
            ],
        ];
        if (1 === count($config)) {
            $args['$config'] = reset($config);
        }
        $client = new Definition(Client::class, $args);
        $client->setPublic(false);

        $container->setDefinition(Client::class, $client);
        $container->setAlias('biig_elasticsearch.client', Client::class);
    }

    private function setMappingFolders(ContainerBuilder $container, $folders)
    {
        $builderDef = $container->getDefinition(IndexBuilder::class);
        $defaultFolder = $container->getParameter('kernel.root_dir') . '/../config/elasticsearch';

        if (empty($folders)) {
            if (!is_dir($defaultFolder)) {
                throw new \InvalidArgumentException(\sprintf('The mapping folder "%s" doesn\'t exist.', $defaultFolder));
            }
            $folders = [$defaultFolder];
        }

        $builderDef->setArgument(1, $folders);
    }

    public function getAlias()
    {
        return 'biig_elasticsearch';
    }
}
