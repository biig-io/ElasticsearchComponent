<?php

namespace Biig\Component\Elasticsearch\Test\Mapping;

use Biig\Component\Elasticsearch\Command\IndexBuilderCommand;
use Biig\Component\Elasticsearch\Mapping\IndexBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class IndexBuilderCommandTest extends TestCase
{
    public function testItOverrideMappingFoldersOnPurpose()
    {
        $customDirectory = __DIR__ . '/foo';
        $builder = $this->prophesize(IndexBuilder::class);
        $builder->setMappingFolders([$customDirectory])->shouldBeCalled();
        $builder->create()->shouldBeCalled();

        $command = new IndexBuilderCommand($builder->reveal());
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            '--dir' => [$customDirectory],
        ]);
    }

    public function testItUsesSuffixIfSpecified()
    {
        $builder = $this->prophesize(IndexBuilder::class);
        $builder->setSuffix('_foo')->shouldBeCalled();
        $builder->create()->shouldBeCalled();

        $command = new IndexBuilderCommand($builder->reveal());
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            '--suffix' => '_foo',
        ]);
    }
}
