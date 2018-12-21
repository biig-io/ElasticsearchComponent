<?php

namespace Biig\Component\Elasticsearch\Exception;

class DocumentNotFoundException extends \RuntimeException
{
    public function __construct(\Throwable $previous = null)
    {
        parent::__construct(
            'Document not found',
            0,
            $previous
        );
    }
}
