<?php
namespace OxidEsales\TestModule\Model;

class TestModel
{
    protected function testMethod()
    {
        $query = "SELECT attribute1, attribute2 FROM testTable";
        $result = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->select($query);
        $count = 0;
        if ($result != false && $result->count() > 0) {
            while (!$result->EOF) {
                $object = new \stdClass();
                if ($result->fields['attribute1'] === true) {
                    $object->setAttribute1($result->fields['attribute2']);
                    $object->save();
                }
                $result->fetchRow();
            }
        }
        return $count;
    }
}
?>
-----
<?php
namespace OxidEsales\TestModule\Model;

class TestModel
{
    protected function testMethod()
    {
        $query = "SELECT attribute1, attribute2 FROM testTable";
        $result = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->select($query);
        $count = 0;
        if ($result != false && $result->count() > 0) {
            while (!$result->EOF) {
                $object = new \stdClass();
                if ($result->fields['attribute1'] === true) {
                    $object->setAttribute1($result->fields['attribute2']);
                    $object->save();
                }
                $result->fetchRow();
            }
        }
        return $count;
    }
}
?>