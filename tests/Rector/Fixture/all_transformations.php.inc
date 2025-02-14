<?php
namespace OxidEsales\TestModule\Controller;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Facts\Facts;
use OxidEsales\EshopCommunity\Internal\Framework\Database\TransactionServiceInterface;

class TestController
{
    public function __construct(private TransactionServiceInterface $transaction)
    {
    }

    public function process()
    {
        $config = Registry::getConfig();
        $wrongParam = $config->getConfigParam('testParam');
        $param = $config->getConfigParam('sSSLShopURL');
        $facts = new Facts();
        if ($facts->isEnterprise()) {
            $path = $facts->getEnterpriseEditionRootPath();
        }
        $this->transaction->begin();
        try {
            // some operations
            $this->transaction->commit();
        } catch (\Exception $e) {
            $this->transaction->rollback();
            throw $e;
        }
        return $param;
    }
}
?>
-----
<?php
namespace OxidEsales\TestModule\Controller;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Facts\Facts;
use OxidEsales\EshopCommunity\Internal\Framework\Database\TransactionServiceInterface;

class TestController
{
    public function __construct(private \OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionFactoryInterface $transaction)
    {
    }

    public function process()
    {
        $connection = $this->transaction->create();
        $config = Registry::getConfig();
        $wrongParam = $config->getConfigParam('testParam');
        $param = \OxidEsales\EshopCommunity\Core\Di\ContainerFacade::getParameter('oxid_esales.shop_url');
        $facts = new \OxidEsales\EshopCommunity\Internal\Transition\Utility\BasicContext();
        if ($facts->getEdition()->value === \OxidEsales\EshopCommunity\Internal\Framework\Edition\Edition::Enterprise->value) {
            $path = $facts->getEditionSourcePath(\OxidEsales\EshopCommunity\Internal\Framework\Edition\Edition::Enterprise);
        }
        $connection->beginTransaction();
        try {
            // some operations
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
        return $param;
    }
}
?>