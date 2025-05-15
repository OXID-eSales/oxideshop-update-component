<?php
namespace OxidEsales\TestModule\Model;

use Doctrine\DBAL\DriverManager;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;

class TestModel
{
    public function __construct(
        private QueryBuilderFactoryInterface $queryBuilderFactory
    ) {
        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = DriverManager::getConnection(['url' => 'some url']);
        $connection->exec('SELECT 1 FROM testtable LIMIT 1');
        $connection->close();
    }

    protected function testMethod()
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder
            ->insert('testTable')
            ->values([
                'OXID' => ':OXID',
            ])
            ->setParameters([
                'OXID' => 'test',
            ]);
        $queryBuilder->execute();
    }

    public function testMethod2()
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder
            ->select('OXID')
            ->from('testtable');

        $queryBuilder->execute()->fetch();
    }

    public function testMethod3()
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder
            ->select('OXID')
            ->from('testtable');

        return $queryBuilder->execute()->fetchAll();
    }

    private function testMethod4(): void
    {
        $migrations = (new MigrationsBuilder())->build();

        $output = new BufferedOutput();
        $migrations->setOutput($output);
        $neeedsUpdate = $migrations->execute('command:name', 'argument');

        if ($neeedsUpdate) {
            $migrations->execute('command:name', 'argument');
        }
    }
}
?>
-----
<?php
namespace OxidEsales\TestModule\Model;

use Doctrine\DBAL\DriverManager;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;

class TestModel
{
    public function __construct(
        private QueryBuilderFactoryInterface $queryBuilderFactory
    ) {
        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = DriverManager::getConnection(['url' => 'some url']);
        $connection->executeStatement('SELECT 1 FROM testtable LIMIT 1');
        $connection->close();
    }

    protected function testMethod()
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder
            ->insert('testTable')
            ->values([
                'OXID' => ':OXID',
            ])
            ->setParameters([
                'OXID' => 'test',
            ]);
        $queryBuilder->executeStatement();
    }

    public function testMethod2()
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder
            ->select('OXID')
            ->from('testtable');

        $queryBuilder->executeQuery()->fetchAssociative();
    }

    public function testMethod3()
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder
            ->select('OXID')
            ->from('testtable');

        return $queryBuilder->executeQuery()->fetchAllAssociative();
    }

    private function testMethod4(): void
    {
        $migrations = (new MigrationsBuilder())->build();

        $output = new BufferedOutput();
        $migrations->setOutput($output);
        $neeedsUpdate = $migrations->execute('command:name', 'argument');

        if ($neeedsUpdate) {
            $migrations->execute('command:name', 'argument');
        }
    }
}
?>