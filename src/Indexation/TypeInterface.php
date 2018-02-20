<?php

namespace Biig\Component\Elasticsearch\Indexation;

interface TypeInterface
{
    public function getName(): string;

    public function insert($object, $id = null);

    public function update($object, $id);

    public function stageForInsert(array $object, $id = null);

    public function flush();

    public function getPaginator();
}
