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
                if ($result->fields[1] === true) {
                    $object->setAttribute1($result->fields[0]);
                    $object->save();
                }
                $result->fetchRow();
            }
        }
        return $count;
    }

    protected function testMethod2()
    {
        $query = "SELECT attribute1, attribute2 FROM testTable";
        $result = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->select($query);
        $object = new \stdClass();
        if ($result->fields[1] === true) {
            $object->setAttribute1($result->fields[0]);
            $object->save();
        }
        return $object;
    }

    protected function testMethod3()
    {
        $query = "SELECT attribute1, attribute2 FROM testTable";
        $result = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->select($query);
        $count = 0;
        if ($result != false && $result->count() > 0) {
            while (!$result->EOF) {
                $object = new \stdClass();
                if ($result->fields[1] === true) {
                    $object->setAttribute1($result->fields[0]);
                    $object->save();
                } else {
                    $subqueryResult = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->selectLimit($query, 10);
                    $object->setAttribute1($subqueryResult->fields[0]);
                    $object->save();
                }
                $result->fetchRow();
            }
        }
        return $count;
    }

    protected function testMethod4()
    {
        $result = $this->container->getService('thirdparty.service')->select('users', 'id = 1');
        $count = 0;
        if ($result != false && $result->count() > 0) {
            while (!$result->EOF) {
                $object = new \stdClass();
                if ($result->fields[1] === true) {
                    $object->setAttribute1($result->fields[0]);
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
                $resultNumericIndexedFields = array_values($result->fields);
                if ($resultNumericIndexedFields[1] === true) {
                    $object->setAttribute1($resultNumericIndexedFields[0]);
                    $object->save();
                }
                $result->fetchRow();
            }
        }
        return $count;
    }

    protected function testMethod2()
    {
        $query = "SELECT attribute1, attribute2 FROM testTable";
        $result = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->select($query);
        $object = new \stdClass();
        $resultNumericIndexedFields = array_values($result->fields);
        if ($resultNumericIndexedFields[1] === true) {
            $object->setAttribute1($resultNumericIndexedFields[0]);
            $object->save();
        }
        return $object;
    }

    protected function testMethod3()
    {
        $query = "SELECT attribute1, attribute2 FROM testTable";
        $result = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->select($query);
        $count = 0;
        if ($result != false && $result->count() > 0) {
            while (!$result->EOF) {
                $object = new \stdClass();
                $resultNumericIndexedFields = array_values($result->fields);
                if ($resultNumericIndexedFields[1] === true) {
                    $object->setAttribute1($resultNumericIndexedFields[0]);
                    $object->save();
                } else {
                    $subqueryResult = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->selectLimit($query, 10);
                    $subqueryResultNumericIndexedFields = array_values($subqueryResult->fields);
                    $object->setAttribute1($subqueryResultNumericIndexedFields[0]);
                    $object->save();
                }
                $result->fetchRow();
            }
        }
        return $count;
    }

    protected function testMethod4()
    {
        $result = $this->container->getService('thirdparty.service')->select('users', 'id = 1');
        $count = 0;
        if ($result != false && $result->count() > 0) {
            while (!$result->EOF) {
                $object = new \stdClass();
                if ($result->fields[1] === true) {
                    $object->setAttribute1($result->fields[0]);
                    $object->save();
                }
                $result->fetchRow();
            }
        }
        return $count;
    }
}
?>