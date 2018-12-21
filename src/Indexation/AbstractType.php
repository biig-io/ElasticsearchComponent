<?php

namespace Biig\Component\Elasticsearch\Indexation;

use Biig\Component\Elasticsearch\Exception\DocumentNotFoundException;
use Biig\Component\Elasticsearch\Exception\NoElasticaTypeAvailable;
use Biig\Component\Elasticsearch\Indexation\Doctrine\SimplePaginator;
use Elastica\Document;
use Elastica\Exception\ResponseException;
use Elastica\Type;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class AbstractType implements TypeInterface
{
    /**
     * @var Type
     */
    private $type;

    /**
     * @var array
     */
    private $stagedForInsert;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger();
        $this->stagedForInsert = [];
    }

    abstract public function getPaginator(): SimplePaginator;

    public function setType(Type $type)
    {
        $this->type = $type;
    }

    public function insert($object, $id = null)
    {
        if (!$this->type) {
            throw new NoElasticaTypeAvailable();
        }

        $this->type->addDocument(new Document($id, $object));
    }

    public function update($object, $id)
    {
        if (!$this->type) {
            throw new NoElasticaTypeAvailable();
        }

        try {
            $this->type->updateDocument(new Document($id, $object));
        } catch (ResponseException $exception) {
            if ('document_missing_exception' === $exception->getResponse()->getFullError()['type']) {
                throw new DocumentNotFoundException($exception);
            }

            throw $exception;
        }
    }

    public function stageForInsert(array $object, $id = null)
    {
        $this->stagedForInsert[] = new Document($id, $object);
    }

    public function flush()
    {
        if (!$this->type) {
            throw new NoElasticaTypeAvailable();
        }

        if (!empty($this->stagedForInsert)) {
            $this->type->addDocuments($this->stagedForInsert);
        }
        $this->type->getIndex()->refresh();
    }
}
