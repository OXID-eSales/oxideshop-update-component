<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Module\Factory;

use OxidEsales\OxidEshopUpdateComponent\Module\Configuration\ModuleRefactorConfiguration;

interface RectorConfigFactoryInterface
{
    public function createConfigurationFile(
        ModuleRefactorConfiguration $configuration,
        string $modulePath
    ): string;
}
