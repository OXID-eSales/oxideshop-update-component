<?php
namespace OxidEsales\TestModule\Model;

use OxidEsales\Eshop\Core\Database\Adapter\DatabaseInterface;

class TestModel
{
    public function testMethod(DatabaseInterface $database, string $query)
    {
        $database->setFetchMode(DatabaseInterface::FETCH_MODE_ASSOC);
        return $database->getOne($query);
    }
}
?>
-----
<?php
namespace OxidEsales\TestModule\Model;

use OxidEsales\Eshop\Core\Database\Adapter\DatabaseInterface;

class TestModel
{
    public function testMethod(DatabaseInterface $database, string $query)
    {
        return $database->getOne($query);
    }
}
?>