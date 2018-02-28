<?php

namespace Biig\Component\Elasticsearch\Indexation;

use Biig\Component\Elasticsearch\Exception\TypeNotFoundException;
use Biig\Component\Elasticsearch\Indexation\Hydrator\Hydrator;
use Biig\Component\Elasticsearch\Indexation\Hydrator\HydratorFactory;
use Biig\Component\Elasticsearch\Mapping\IndexBuilder;
use Elastica\Client;
use Elastica\Exception\Bulk\ResponseException;
use Elastica\Index;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AbstractIndex.
 */
abstract class AbstractIndex implements IndexInterface
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Index
     */
    private $index;

    /**
     * @var AbstractType[]
     */
    private $types;

    /**
     * @var IndexBuilder
     */
    private $builder;

    /**
     * @var HydratorFactory
     */
    private $hydratorFactory;

    /**
     * @var string
     */
    private $name;

    abstract protected function getInstance(string $name, Client $client, IndexBuilder $builder, HydratorFactory $hydratorFactory, LoggerInterface $logger): IndexInterface;

    public function __construct(string $name, Client $client, IndexBuilder $builder, HydratorFactory $hydratorFactory, LoggerInterface $logger = null)
    {
        $this->client = $client;
        $this->builder = $builder;
        $this->hydratorFactory = $hydratorFactory;
        $this->name = $name;
        $this->logger = $logger ?? new NullLogger();
    }

    public function addType(AbstractType $type)
    {
        $this->types[] = $type;
        $type->setType($this->getIndex()->getType($type->getName()));
    }

    public function exists(): bool
    {
        return $this->getIndex()->exists();
    }

    public function create()
    {
        $this->builder->create($this->getName());
    }

    protected function getIndex(): Index
    {
        if ($this->index) {
            return $this->index;
        }

        return $this->index = $this->client->getIndex($this->getName());
    }

    public function drop()
    {
        $this->getIndex()->delete();
    }

    final public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param array           $object
     * @param string          $type
     * @param string|int|null $id
     *
     * @throws TypeNotFoundException
     */
    public function insert(array $object, string $type, $id = null)
    {
        try {
            $this->getType($type)->insert($object, $id);
        } catch (ResponseException $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    /**
     * @param array      $object
     * @param string     $type
     * @param string|int $id
     *
     * @throws TypeNotFoundException
     */
    public function update(array $object, string $type, $id)
    {
        try {
            $this->getType($type)->update($object, $id);
        } catch (ResponseException $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    public function getType(string $name): TypeInterface
    {
        foreach ($this->types as $type) {
            if ($type->getName() === $name) {
                return $type;
            }
        }

        throw new TypeNotFoundException($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getHydrator($type): Hydrator
    {
        if (!$type instanceof TypeInterface) {
            $type = $this->getType($type);
        }

        return $this->hydratorFactory->create($type);
    }

    public function hydrate(bool $dryRun = false, OutputInterface $output = null)
    {
        foreach ($this->types as $type) {
            $hydrator = $this->getHydrator($type);
            $hydrator($dryRun, $output);
        }
    }

    public function setSuffix(string $suffix): IndexInterface
    {
        return $this->getInstance($this->getName() . $suffix, $this->client, $this->builder, $this->hydratorFactory, $this->logger);
    }
}
