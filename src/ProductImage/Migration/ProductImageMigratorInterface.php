<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\ProductImage\Migration;

use Iterator;

interface ProductImageMigratorInterface
{
    public function migrate(int $batchSize = 5000): Iterator;
}
