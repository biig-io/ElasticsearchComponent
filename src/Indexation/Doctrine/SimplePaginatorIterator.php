<?php

namespace Biig\Component\Elasticsearch\Indexation\Doctrine;

class SimplePaginatorIterator implements \Iterator
{
    /**
     * @var SimplePaginator
     */
    private $paginator;

    /**
     * @var int
     */
    private $currentPage;

    public function __construct(SimplePaginator $paginator)
    {
        $this->paginator = $paginator;
        $this->currentPage = 1;
    }

    /**
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function current()
    {
        return $this->paginator->getPage($this->currentPage);
    }

    public function next()
    {
        ++$this->currentPage;
    }

    public function key()
    {
        return $this->currentPage - 1;
    }

    public function valid()
    {
        return $this->currentPage <= $this->paginator->getLastPage();
    }

    public function rewind()
    {
        $this->currentPage = 1;
    }
}
