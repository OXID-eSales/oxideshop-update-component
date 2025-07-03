<?php
namespace OxidEsales\TestModule;

use OxidEsales\EshopCommunity\Internal\Container\ContainerBuilderFactory;

class ModuleEvents
{
    public static function onActivate(): void
    {
        $containerBuilder = (new ContainerBuilderFactory())->create();
        $container = $containerBuilder->getContainer();
        $container->compile();
        
        $myService = $container->get('service-id');
        $myService->doSomething();
    }
}
?>
-----
<?php
namespace OxidEsales\TestModule;

use OxidEsales\EshopCommunity\Internal\Container\ContainerBuilderFactory;

class ModuleEvents
{
    public static function onActivate(): void
    {
        $containerBuilder = new \OxidEsales\EshopCommunity\Internal\Framework\DIContainer\ContainerBuilder(new \OxidEsales\EshopCommunity\Internal\Transition\Utility\BasicContext(), (new \OxidEsales\EshopCommunity\Internal\Transition\ShopEvents\ShopIdCalculator(new \OxidEsales\EshopCommunity\Core\UtilsServer()))->getShopId());
        $container = $containerBuilder->getContainer();
        $container->compile();
        
        $myService = $container->get('service-id');
        $myService->doSomething();
    }
}
?>