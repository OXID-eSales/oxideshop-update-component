<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

use OxidEsales\OxidEshopUpdateComponent\Module\Rector\Rules\TransactionConnectionRule;
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(TransactionConnectionRule::class);

    $rectorConfig->ruleWithConfiguration(
        RenameClassRector::class,
        [
            'OxidEsales\EshopCommunity\Internal\Framework\Database\TransactionServiceInterface' =>
                'OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionFactoryInterface',
        ]
    );
};
