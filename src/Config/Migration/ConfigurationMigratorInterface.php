<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\Config\Migration;

interface ConfigurationMigratorInterface
{
    public function migrate(): void;
}
