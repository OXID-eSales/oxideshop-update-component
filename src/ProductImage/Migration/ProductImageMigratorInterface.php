<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\ProductImage\Migration;

use Iterator;

interface ProductImageMigratorInterface
{
    public function migrateProducts(int $batchSize = 5000): Iterator;

    public function migrateVariants(int $batchSize = 5000): Iterator;
}
