<?php

namespace Biig\Component\Elasticsearch\Indexation;

use Biig\Component\Elasticsearch\Indexation\Hydrator\Hydrator;
use Symfony\Component\Console\Output\OutputInterface;

interface IndexInterface
{
    public function getName(): string;

    public function exists(): bool;

    public function drop();

    public function create();

    public function insert(array $object, string $type, $id = null);

    public function update(array $object, string $type, $id);

    public function getType(string $name): TypeInterface;

    public function hydrate(bool $dryRun = false, OutputInterface $output = null);

    /**
     * @param string|TypeInterface $type
     *
     * @return Hydrator
     */
    public function getHydrator($type): Hydrator;

    /**
     * Return a new instance of the index for SOLID needs.
     *
     * @param string $suffix
     *
     * @return IndexInterface
     */
    public function setSuffix(string $suffix): self;
}
