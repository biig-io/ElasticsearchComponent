<?php

namespace Biig\Component\Elasticsearch\Exception;

use Throwable;

class IndexationError extends \Exception
{
    public function __construct(string $message = 'Error while indexing', int $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            print_r($message, true),
            $code,
            $previous
        );
    }
}
