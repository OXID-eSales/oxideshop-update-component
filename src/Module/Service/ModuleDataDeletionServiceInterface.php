<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\Module\Service;

/**
 * @internal
 */
interface ModuleDataDeletionServiceInterface
{
    public function deleteModuleDataFromDatabase(): void;
}
