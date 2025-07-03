<?php
namespace OxidEsales\TestModule;

use OxidEsales\Facts\Facts;

class FactsTest
{
    public function checkEdition()
    {
        $facts = new Facts();
        
        if ($facts->isEnterprise()) {
            $path = $facts->getEnterpriseEditionRootPath();
        } elseif ($facts->isProfessional()) {
            $path = $facts->getProfessionalEditionRootPath();
        } else {
            $path = $facts->getCommunityEditionRootPath();
        }
        
        return $path;
    }
}
?>
-----
<?php
namespace OxidEsales\TestModule;

use OxidEsales\Facts\Facts;

class FactsTest
{
    public function checkEdition()
    {
        $facts = new \OxidEsales\EshopCommunity\Internal\Transition\Utility\BasicContext();
        
        if ($facts->getEdition()->value === \OxidEsales\EshopCommunity\Internal\Framework\Edition\Edition::Enterprise->value) {
            $path = $facts->getEditionSourcePath(\OxidEsales\EshopCommunity\Internal\Framework\Edition\Edition::Enterprise);
        } elseif ($facts->getEdition()->value === \OxidEsales\EshopCommunity\Internal\Framework\Edition\Edition::Professional->value) {
            $path = $facts->getEditionSourcePath(\OxidEsales\EshopCommunity\Internal\Framework\Edition\Edition::Professional);
        } else {
            $path = $facts->getEditionSourcePath(\OxidEsales\EshopCommunity\Internal\Framework\Edition\Edition::Community);
        }
        
        return $path;
    }
}
?>