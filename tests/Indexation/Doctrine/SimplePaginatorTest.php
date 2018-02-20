<?php

namespace Biig\Component\Elasticsearch\Test\Indexation\Doctrine;

use Biig\Component\Elasticsearch\Indexation\Doctrine\SimplePaginator;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

//use Biig\Component\Elasticsearch\Test\Fixtures\Dummy;

class SimplePaginatorTest extends TestCase
{
    private $dbpath;

    /**
     * @var EntityManager
     */
    private $entityManager;

    public function setUp()
    {
        $this->dbpath = \sys_get_temp_dir() . '/testElasticComponentPaginator.' . \microtime() . '.sqlite';

        $faker = Factory::create();
        $config = Setup::createConfiguration();
        $driver = new AnnotationDriver(new AnnotationReader(), [__DIR__]);

        AnnotationRegistry::registerLoader('class_exists');
        $config->setMetadataDriverImpl($driver);
        $conn = [
            'driver' => 'pdo_sqlite',
            'path' => $this->dbpath,
        ];
        $this->entityManager = EntityManager::create($conn, $config);
        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->createSchema($this->entityManager->getMetadataFactory()->getAllMetadata());

        for ($i = 0; $i < 100000; ++$i) {
            $dummy = new Dummy();
            $dummy->setContent($faker->text);
            $this->entityManager->persist($dummy);

            // Optimization for inserting because it uses too much memory
            if ($i % 10) {
                $this->entityManager->flush();
                $this->entityManager->clear();
            }
        }
    }

    public function tearDown()
    {
        @unlink($this->dbpath);
    }

    /**
     * @group huge
     */
    public function testItPaginatesQueries()
    {
        $query = $this->entityManager->getRepository(Dummy::class)->createQueryBuilder('d');
        $simplePaginator = new SimplePaginator($query, 500);

        $this->assertEquals(100000, $simplePaginator->count());
        $this->assertEquals(200, $simplePaginator->getLastPage());

        $before = round(memory_get_usage() / 1024 / 1024);
        $count = 0;
        foreach ($simplePaginator as $page) {
            foreach ($page as $item) {
                ++$count;
            }
        }
        $after = round(memory_get_usage() / 1024 / 1024, 2);

        // Assert that there is no memory leak
        $this->assertTrue($after < ($before + 2));
        $this->assertEquals(100000, $count); // All dummies were retrieve
    }
}

/**
 * Class Dummy.
 *
 * @ORM\Entity
 */
class Dummy
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    private $content;

    public function getId()
    {
        return $this->id;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }
}
