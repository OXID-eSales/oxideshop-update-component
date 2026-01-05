<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\Module\Update;

use OxidEsales\OxidEshopUpdateComponent\Module\Configuration\ModuleRefactorConfiguration;

interface ModuleUpdaterInterface
{
    public function update(string $modulePath, ModuleRefactorConfiguration $configuration): void;
}
