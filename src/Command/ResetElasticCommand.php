<?php

namespace Biig\Component\Elasticsearch\Command;

use Biig\Component\Elasticsearch\Exception\AlreadyExistingIndexException;
use Biig\Component\Elasticsearch\Exception\InvalidArgumentException;
use Biig\Component\Elasticsearch\Indexation\IndexInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ResetElasticCommand extends Command
{
    /**
     * @var string
     */
    private $suffix;

    /**
     * @var IndexInterface[]
     */
    private $indexes;

    public function __construct(array $indexes, string $suffix = '')
    {
        parent::__construct();

        if (empty($indexes)) {
            throw new InvalidArgumentException('The command reset elastic cannot reset nothing. Please specify indexes.');
        }

        $this->indexes = $indexes;
        $this->suffix = $suffix;
    }

    protected function configure()
    {
        $this
            ->setName('biig:elasticsearch:reset')
            ->setDescription('Reset your elasticsearch indexes.')
            ->setHelp(<<<TEXT
This command helps you to regenerate your indexes.

You can choose to drop old index or not. (default: no)

The option `suffix` allows you to generate different indexes than those
currently used in your production environment. This is very useful to avoid
down time on your production elasticsearch.
TEXT
)
            ->addOption(
                'suffix',
                null,
                InputOption::VALUE_OPTIONAL,
                'The suffix of generated indexes.',
                $this->suffix
            )
            ->addOption(
                'drop-if-exists',
                null,
                InputOption::VALUE_NONE,
                'If one or many indexes exist, they will be drop before re-creating them.'
            )
            ->addOption(
                'index',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'If you want to process only some indexes.'
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Will run this command without trying any action on the remote indexes. It will try to connect to ES.'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $startMemory = memory_get_usage();
        $indexes = $this->getIndexes(!empty($input->getOption('index')) ? $input->getOption('index') : []);

        if (!$input->getOption('drop-if-exists')) {
            foreach ($indexes as $index) {
                if ($index->exists()) {
                    throw new AlreadyExistingIndexException(
                        sprintf(
                            'The index %s exists already. I will not drop it until you ask me to do it.',
                            $index->getName()
                        )
                    );
                }
            }
        }

        $output->writeln("<comment>Starting to reset the elasticsearch indexes.</comment>\n");

        foreach ($indexes as $index) {
            $output->writeln('<comment>Starting to reset the index "' . $index->getName() . '".</comment>');

            if ($index->exists()) {
                $output->writeln('<comment>The lead index exists in ES. I drop it.</comment>');
                $index->drop();
            }
            $index->create();
            $output->writeln('<comment>Created index "' . $index->getName() . '".</comment>');
            $index->hydrate($input->getOption('dry-run'), $output);
        }

        if (OutputInterface::VERBOSITY_VERBOSE === $output->getVerbosity()) {
            $output->writeln(
                sprintf(
                    '<comment>This command used %s MiB of RAM.</comment>',
                    round((memory_get_usage() - $startMemory) / 1024 / 1024, 3)
                )
            );
        }
    }

    /**
     * @param string[] $indexes
     *
     * @return IndexInterface[]
     */
    private function getIndexes(array $indexes): array
    {
        if (empty($indexes)) {
            return $this->injectSuffix($this->indexes);
        }

        $output = array_filter($this->indexes, function (IndexInterface $item) use ($indexes) {
            return in_array($item->getName(), $indexes);
        });

        return $this->injectSuffix($output);
    }

    /**
     * @param IndexInterface[] $indexes
     *
     * @return IndexInterface[]
     */
    private function injectSuffix(array $indexes)
    {
        if ('' === $this->suffix) {
            return $indexes;
        }

        $output = [];

        foreach ($indexes as $index) {
            $output[] = $index->setSuffix($this->suffix);
        }

        return $output;
    }
}
