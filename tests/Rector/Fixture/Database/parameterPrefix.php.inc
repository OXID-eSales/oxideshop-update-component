<?php
namespace OxidEsales\TestModule\Model;

use OxidEsales\Eshop\Core\Database\Adapter\DatabaseInterface;

class TestModel
{
    public function testMethod($attribute1, $testObject)
    {
        $database = \OxidEsales\Eshop\Core\DatabaseProvider::getDb(DatabaseInterface::FETCH_MODE_ASSOC);

        $query = 'select max(testtable.attribute3) from testtable where testtable.attribute1 = :attribute1
                  and testtable.attribute2 = :attribute2 ';

        $parameters = [
            ':attribute1' => $attribute1,
            ':attribute2' => $testObject->getShopId()
        ];
        return $database->getOne($query, $parameters);
    }

    public function testMethod2(DatabaseInterface $database, string $attribute1, string $attribute2)
    {
        $database->select('Query', [':attribute1' => $attribute1, ':attribute2' => $attribute2]);
    }
}
?>
-----
<?php
namespace OxidEsales\TestModule\Model;

use OxidEsales\Eshop\Core\Database\Adapter\DatabaseInterface;

class TestModel
{
    public function testMethod($attribute1, $testObject)
    {
        $database = \OxidEsales\Eshop\Core\DatabaseProvider::getDb();

        $query = 'select max(testtable.attribute3) from testtable where testtable.attribute1 = :attribute1
                  and testtable.attribute2 = :attribute2 ';

        $parameters = [
            'attribute1' => $attribute1,
            'attribute2' => $testObject->getShopId()
        ];
        return $database->getOne($query, $parameters);
    }

    public function testMethod2(DatabaseInterface $database, string $attribute1, string $attribute2)
    {
        $database->select('Query', ['attribute1' => $attribute1, 'attribute2' => $attribute2]);
    }
}
?>