<?php

namespace Biig\Component\Elasticsearch\Indexation\Hydrator;

use Biig\Component\Elasticsearch\Exception\NoElasticaTypeAvailable;
use Biig\Component\Elasticsearch\Indexation\TypeInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class Hydrator
{
    /**
     * @var TypeInterface
     */
    private $type;

    /**
     * @var NormalizerInterface
     */
    private $serializer;

    public function __construct(TypeInterface $type, NormalizerInterface $serializer)
    {
        $this->type = $type;
        $this->serializer = $serializer;
    }

    /**
     * @param bool                 $dryRun
     * @param OutputInterface|null $output
     *
     * @throws NoElasticaTypeAvailable
     */
    public function __invoke($dryRun = false, OutputInterface $output = null)
    {
        if (!$this->type) {
            throw new NoElasticaTypeAvailable();
        }

        if (null === $output) {
            $output = new NullOutput();
        }
        $output->writeln(sprintf(
            "\n<comment>Starting to hydrate type \"%s\"...</comment>",
            $this->type->getName()
        ));

        $paginator = $this->type->getPaginator();
        $progress = new ProgressBar($output, count($paginator));
        $progress->start();
        $progression = 0;

        foreach ($paginator as $page) {
            foreach ($page as $item) {
                if (!$dryRun) {
                    $id = null;
                    if (method_exists($item, 'getId')) {
                        $id = $item->getId();
                    }
                    $this->type->stageForInsert($this->serializer->normalize($item, '', ['groups' => ['elasticsearch']]), $id);
                    ++$progression;
                }
                $progress->advance();
            }
            $this->type->flush();
        }
        $progress->finish();

        $output->writeln(
            sprintf(
                "\n<info><options=bold>%s</> objects were inserted in the new ES index \"%s\".</info>",
                $progression,
                $this->type->getName()
            )
        );
    }
}
