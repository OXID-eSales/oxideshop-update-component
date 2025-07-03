<?php
namespace OxidEsales\TestModule;

use OxidEsales\Eshop\Core\Registry;

class ConfigTest
{
    public function getShopUrl()
    {
        $config = Registry::getConfig();
        $sslUrl = $config->getConfigParam('sSSLShopURL');
        $shopUrl = $config->getConfigParam('sShopURL');
        
        return $sslUrl ?: $shopUrl;
    }
}
?>
-----
<?php
namespace OxidEsales\TestModule;

use OxidEsales\Eshop\Core\Registry;

class ConfigTest
{
    public function getShopUrl()
    {
        $config = Registry::getConfig();
        $sslUrl = \OxidEsales\EshopCommunity\Core\Di\ContainerFacade::getParameter('oxid_esales.shop_url');
        $shopUrl = \OxidEsales\EshopCommunity\Core\Di\ContainerFacade::getParameter('oxid_esales.shop_url');
        
        return $sslUrl ?: $shopUrl;
    }
}
?>