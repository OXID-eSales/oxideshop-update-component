<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

use OxidEsales\OxidEshopUpdateComponent\Module\Rector\Rules\ConfigParamReplacementRule;
use Rector\Config\RectorConfig;
use Rector\Transform\Rector\MethodCall\MethodCallToStaticCallRector;
use Rector\Transform\ValueObject\MethodCallToStaticCall;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(ConfigParamReplacementRule::class);
};
