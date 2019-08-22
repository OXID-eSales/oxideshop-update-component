<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\Adapter;

use OxidEsales\Eshop\Core\Registry;

/**
 * @internal
 */
class ShopAdapter
{
    public function parseModuleChains(array $extensionsFromDatabase): array
    {
        return Registry::getConfig()->parseModuleChains($extensionsFromDatabase);
    }
}
