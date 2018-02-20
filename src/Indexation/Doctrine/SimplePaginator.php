<?php

namespace Biig\Component\Elasticsearch\Indexation\Doctrine;

use Biig\Component\Elasticsearch\Exception\PageNotFoundException;
use Biig\Component\Elasticsearch\Indexation\DataPaginatorInterface;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Paginate data to avoid getting out of memory.
 */
class SimplePaginator implements DataPaginatorInterface
{
    /**
     * @var Query
     */
    private $query;

    /**
     * @var int
     */
    private $byPage;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * Cache variable.
     *
     * @var int
     */
    private $count;

    /**
     * @param QueryBuilder $builder
     * @param int          $byPage  Default to 200 because
     */
    public function __construct(QueryBuilder $builder, $byPage = 200)
    {
        $this->query = $builder->getQuery();
        $this->em = $builder->getEntityManager();
        $this->byPage = $byPage;
    }

    /**
     * Iterates over pages.
     */
    public function getIterator(): \Iterator
    {
        return new SimplePaginatorIterator($this);
    }

    /**
     * Retrieval of a new page generates a clear of the EntityManager
     * and executes a new query.
     *
     * @param int $page
     *
     * @return Paginator
     */
    public function getPage($page = 1)
    {
        $this->em->clear();
        $lastPage = $this->getLastPage();
        if ($page < 0 || $page > $lastPage) {
            throw new PageNotFoundException($page);
        }

        $query = $this->getQuery();
        $firstResult = ($page - 1) * $this->byPage;
        $query->setFirstResult($firstResult);

        $restToPaginate = $this->count() - $firstResult;
        $query->setMaxResults($restToPaginate < $this->byPage ? $restToPaginate : $this->byPage);

        return new Paginator($query);
    }

    /**
     * @return int
     */
    public function getLastPage(): int
    {
        return (int) ceil($this->count() / $this->byPage) ?: 1;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        if (null !== $this->count) {
            return $this->count;
        }

        $countQuery = $this->getQuery();
        $this->appendTreeWalker($countQuery, 'Doctrine\ORM\Tools\Pagination\CountWalker');
        $countQuery->setFirstResult(null)->setMaxResults(null);

        try {
            $this->count = array_sum(array_map('current', $countQuery->getScalarResult()));
        } catch (NoResultException $e) {
            $this->count = 0;
        }

        return $this->count;
    }

    /**
     * @return Query
     */
    private function getQuery()
    {
        $query = clone $this->query;
        $query->setParameters(clone $this->query->getParameters());
        $query->setCacheable(false);

        foreach ($this->query->getHints() as $name => $value) {
            $query->setHint($name, $value);
        }

        return $query;
    }

    /**
     * Appends a custom tree walker to the tree walkers hint.
     * This method comes from the Doctrine Paginator.
     *
     * @param Query  $query
     * @param string $walkerClass
     */
    private function appendTreeWalker(Query $query, $walkerClass)
    {
        $hints = $query->getHint(Query::HINT_CUSTOM_TREE_WALKERS);

        if (false === $hints) {
            $hints = [];
        }

        $hints[] = $walkerClass;
        $query->setHint(Query::HINT_CUSTOM_TREE_WALKERS, $hints);
    }
}
