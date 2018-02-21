<?php

namespace Biig\Component\Elasticsearch\Indexation;

use Elastica\Client;
use Elastica\Document;
use Elastica\Exception\Bulk\ResponseException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class AbstractIndex.
 */
abstract class AbstractIndex
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Client          $client
     * @param LoggerInterface $logger
     */
    public function __construct(Client $client, LoggerInterface $logger = null)
    {
        $this->client = $client;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @return string
     */
    abstract public function getName();

    /**
     * @return string
     */
    abstract public function getTypeName();

    /**
     * @param $object
     * @param null $id
     *
     * @return \Elastica\Bulk\ResponseSet
     */
    public function insert($object, $id = null)
    {
        try {
            $document = $this->createDocument($id, $object);

            return $this->client->addDocuments([$document]);
        } catch (ResponseException $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    /**
     * @param $object
     * @param null $id
     *
     * @return \Elastica\Bulk\ResponseSet
     */
    public function update($object, $id = null)
    {
        try {
            $document = $this->createDocument($id, $object);

            return $this->client->updateDocuments([$document]);
        } catch (ResponseException $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    private function createDocument($id, array $data)
    {
        return new Document($id, $data, $this->getTypeName(), $this->getName());
    }
}
