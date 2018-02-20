<?php

namespace Biig\Component\Elasticsearch\Exception;

class PageNotFoundException extends \RuntimeException
{
    public function __construct($page)
    {
        parent::__construct(
            sprintf('The page %s is not available in this paginator. You\'re code may be malformed.', $page),
            0,
            null
        );
    }
}
