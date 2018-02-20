<?php

namespace Biig\Component\Elasticsearch\Integration\Symfony\DependencyInjection\CompilerPass;

use Biig\Component\Elasticsearch\Command\ResetElasticCommand;
use Biig\Component\Elasticsearch\Integration\Symfony\DependencyInjection\ElasticsearchExtension;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AddIndexesToResetCommandCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(ResetElasticCommand::class)) {
            return;
        }

        $definition = $container->findDefinition(ResetElasticCommand::class);

        $taggedServices = $container->findTaggedServiceIds(ElasticsearchExtension::INDEX_TAG);

        $references = [];
        foreach ($taggedServices as $id => $tags) {
            $references[] = new Reference($id);
        }

        $definition->setArgument(0, $references);
    }
}
