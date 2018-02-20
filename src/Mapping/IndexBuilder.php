<?php

namespace Biig\Component\Elasticsearch\Mapping;

use Elastica\Client;
use Symfony\Component\Finder\Finder;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Yaml\Yaml;

class IndexBuilder
{
    /**
     * @var string[]
     */
    private $mappingFolders;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $suffix;

    public function __construct(Client $client, array $mappingFolders, string $suffix = '')
    {
        $this->client = $client;
        $this->suffix = $suffix;
        $this->setMappingFolders($mappingFolders);
    }

    /**
     * Create the indexes inside elasticsearch based on the given mapping files.
     *
     * @param string $index Specify this argument if you want to build only 1 specific
     *
     * @return string[]
     */
    public function create(string $index = null)
    {
        $finder = new Finder();
        $finder->in($this->mappingFolders);
        $finder->name('*.yaml');
        $createdIndexes = [];

        foreach ($finder as $file) {
            $indexToCreate = $file->getBasename('.yaml');

            if (null !== $index && $indexToCreate !== $index) {
                continue;
            }

            $settings = Yaml::parse(file_get_contents($file->getRealPath()));

            $index = $this->client->getIndex($indexToCreate . $this->suffix);
            $index->create($settings, true);
            $createdIndexes[] = $indexToCreate;
        }

        return $createdIndexes;
    }

    /**
     * Specify custom mapping folders.
     *
     * @param array $mappingFolders
     */
    public function setMappingFolders(array $mappingFolders)
    {
        $resolver = new OptionsResolver();

        $resolver->setRequired('mappingFolders');
        $resolver->setAllowedTypes('mappingFolders', 'string[]');
        $resolver->addAllowedValues('mappingFolders', function ($folders) {
            foreach ($folders as $folder) {
                if (!is_dir($folder)) {
                    return false;
                }
            }

            return true;
        });

        $this->mappingFolders = $resolver->resolve(['mappingFolders' => $mappingFolders])['mappingFolders'];
    }

    public function setSuffix(string $suffix)
    {
        $this->suffix = $suffix;
    }
}
