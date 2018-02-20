<?php

namespace Biig\Component\Elasticsearch\Integration\Symfony;

use Biig\Component\Elasticsearch\Integration\Symfony\DependencyInjection\CompilerPass\AddIndexesToResetCommandCompilerPass;
use Biig\Component\Elasticsearch\Integration\Symfony\DependencyInjection\ElasticsearchExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ElasticsearchBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new ElasticsearchExtension();
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AddIndexesToResetCommandCompilerPass());
    }
}
