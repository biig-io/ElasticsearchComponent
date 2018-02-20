<?php

namespace Biig\Component\Elasticsearch\Test\Command;

use Biig\Component\Elasticsearch\Command\ResetElasticCommand;
use Biig\Component\Elasticsearch\Indexation\IndexInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Console\Tester\CommandTester;

class ResetElasticCommandTest extends TestCase
{
    /**
     * @expectedException \Biig\Component\Elasticsearch\Exception\AlreadyExistingIndexException
     */
    public function testItFailIfIndexExistsAlready()
    {
        $indexes = [
            $this->fakeIndex('foo', true)->reveal(),
        ];

        $command = new ResetElasticCommand($indexes);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
    }

    public function testItResetOnlyGivenIndexes()
    {
        $indexes = [
            $this->fakeIndex('foo', false, true, true)->reveal(),
            $this->fakeIndex('bar', false, true, true)->reveal(),
            $this->fakeIndex('baz', false, false, false)->reveal(),
        ];

        $command = new ResetElasticCommand($indexes);
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            '--index' => ['foo', 'bar'],
        ]);
    }

    public function testItResetAllIndexesIfNoneSpecified()
    {
        $indexes = [
            $this->fakeIndex('foo', false, true, true)->reveal(),
            $this->fakeIndex('bar', false, true, true)->reveal(),
            $this->fakeIndex('baz', false, true, true)->reveal(),
        ];

        $command = new ResetElasticCommand($indexes);
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
    }

    private function fakeIndex($name, $exists, $create = null, $hydrate = null, $drop = null)
    {
        $index = $this->prophesize(IndexInterface::class);
        $index->getName()->willReturn($name);
        $index->exists()->willReturn($exists);

        if (null !== $drop) {
            $index->drop()->willReturn();
        }

        if (null !== $create) {
            if ($create) {
                $index->create()->shouldBeCalled();
            } else {
                $index->create()->shouldNotBecalled();
            }
        }

        if (null !== $hydrate) {
            if ($hydrate) {
                $index->hydrate(Argument::cetera())->shouldBeCalled();
            } else {
                $index->hydrate(Argument::cetera())->shouldNotBecalled();
            }
        }

        return $index;
    }
}
