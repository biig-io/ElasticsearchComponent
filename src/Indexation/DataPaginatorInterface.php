<?php

namespace Biig\Component\Elasticsearch\Indexation;

interface DataPaginatorInterface extends \Countable, \IteratorAggregate
{
    public function getIterator(): \Iterator;

    public function count(): int;

    public function getLastPage(): int;
}
