<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

use OxidEsales\OxidEshopUpdateComponent\Module\Rector\Rules\ContainerBuilderFactoryReplacementRule;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(ContainerBuilderFactoryReplacementRule::class);
};
