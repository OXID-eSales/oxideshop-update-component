<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\Module\Service;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Adapter\Configuration\Dao\ShopConfigurationSettingDaoInterface;
use OxidEsales\EshopCommunity\Internal\Common\Exception\EntryDoesNotExistDaoException;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\Dao\ShopConfigurationDaoInterface;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\ModuleConfiguration;
use OxidEsales\EshopCommunity\Internal\Module\Setting\SettingDaoInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
class ModuleDataDeletionService implements ModuleDataDeletionServiceInterface
{
    public function __construct(
        OutputInterface $output
    ) {
        $this->output = $output;
    }

    public function deleteModuleDataFromDatabase(): void
    {
        // TODO: Implement deleteModuleDataFromDatabase() method.
    }


}
