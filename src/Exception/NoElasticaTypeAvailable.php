<?php

namespace Biig\Component\Elasticsearch\Exception;

use Throwable;

class NoElasticaTypeAvailable extends \Exception
{
    public function __construct(int $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            'You need to provide an elastica type with the setter to make this class work as expected.',
            $code,
            $previous
        );
    }
}
