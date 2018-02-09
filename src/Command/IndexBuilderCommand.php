<?php

namespace Biig\Component\Elasticsearch\Command;

use Biig\Component\Elasticsearch\Mapping\IndexBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class IndexBuilderCommand extends Command
{
    /**
     * @var string
     */
    private $suffix;

    /**
     * @var IndexBuilder
     */
    private $builder;

    public function __construct(IndexBuilder $builder, string $suffix = null)
    {
        parent::__construct();
        $this->suffix = $suffix;
        $this->builder = $builder;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('biig:elasticsearch:build')
            ->setDescription('Build your elasticsearch indexes.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp(<<<TEXT
This command generates elasticsearch indexes based on a YAML mapping configuration.

The option `suffix` allows you to generate different indexes than those
currently used in your production environment. This is very useful to avoid
down time on your production elasticsearch.
TEXT
)
            ->addOption(
                'suffix',
                null,
                InputOption::VALUE_OPTIONAL,
                'The suffix of generated indexes.'
            )
            ->addOption(
                'dir',
                'd',
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'Directory to find mapping information.'
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->hasOption('dir') && !empty($input->getOption('dir'))) {
            $this->builder->setMappingFolders($input->getOption('dir'));
        }

        if (!empty($this->suffix)) {
            $this->builder->setSuffix($this->suffix);
        }

        if ($input->hasOption('suffix') && !is_null($input->getOption('suffix'))) {
            $this->builder->setSuffix($input->getOption('suffix'));
        }

        $this->builder->create();
    }
}
