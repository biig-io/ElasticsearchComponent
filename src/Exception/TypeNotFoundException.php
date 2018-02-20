<?php

namespace Biig\Component\Elasticsearch\Exception;

use Throwable;

class TypeNotFoundException extends \Exception
{
    public function __construct(string $type, int $code = 0, Throwable $previous = null)
    {
        parent::__construct(sprintf(
            'Impossible to find the type "%". The type is maybe not registered or does not exist ?',
            $type
        ), $code, $previous);
    }
}
