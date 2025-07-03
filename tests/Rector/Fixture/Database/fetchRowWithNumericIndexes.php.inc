<?php
namespace OxidEsales\TestModule\Model;

class TestModel
{
    protected function testMethod()
    {
        $query = "SELECT attribute1, attribute2 FROM testTable";
        $row = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getRow($query);
        $object = new \stdClass();
        if ($row[1] === true) {
            $object->setAttribute1($row[0]);
            $object->save();
        }

        return $object;
    }

    protected function testMethod2()
    {
        $query = "SELECT attribute1, attribute2 FROM testTable";
        $database = \OxidEsales\Eshop\Core\DatabaseProvider::getMaster();
        $row = $database->getRow($query);
        $object = new \stdClass();
        if ($row[1] === true) {
            $object->setAttribute1($row[0]);
            $object->save();
        }

        return $object;
    }

    protected function testMethod3()
    {
        $row = $this->container->getService('thirdparty.service')->getRow('users');
        $object = new \stdClass();
        if ($row[1] === true) {
            $object->setAttribute1($row[0]);
            $object->save();
        }

        return $object;
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
        $row = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getRow($query);
        $numericIndexedFields = array_values($row);
        $object = new \stdClass();
        if ($numericIndexedFields[1] === true) {
            $object->setAttribute1($numericIndexedFields[0]);
            $object->save();
        }

        return $object;
    }

    protected function testMethod2()
    {
        $query = "SELECT attribute1, attribute2 FROM testTable";
        $database = \OxidEsales\Eshop\Core\DatabaseProvider::getMaster();
        $row = $database->getRow($query);
        $numericIndexedFields = array_values($row);
        $object = new \stdClass();
        if ($numericIndexedFields[1] === true) {
            $object->setAttribute1($numericIndexedFields[0]);
            $object->save();
        }

        return $object;
    }

    protected function testMethod3()
    {
        $row = $this->container->getService('thirdparty.service')->getRow('users');
        $object = new \stdClass();
        if ($row[1] === true) {
            $object->setAttribute1($row[0]);
            $object->save();
        }

        return $object;
    }
}
?>