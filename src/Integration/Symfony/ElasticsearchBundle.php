<?php

namespace Biig\Component\Elasticsearch\Integration\Symfony;

use Biig\Component\Elasticsearch\Integration\Symfony\DependencyInjection\ElasticsearchExtension;
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
}
