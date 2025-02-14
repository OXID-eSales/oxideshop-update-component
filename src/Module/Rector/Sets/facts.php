<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

use OxidEsales\OxidEshopUpdateComponent\Module\Rector\Rules\FactsReplacementRule;
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\RenameClassAndConstFetch;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(FactsReplacementRule::class);

    $rectorConfig->ruleWithConfiguration(
        RenameClassRector::class,
        [
            'OxidEsales\Facts\Facts' => 'OxidEsales\EshopCommunity\Internal\Transition\Utility\BasicContext',
        ]
    );

    $rectorConfig->ruleWithConfiguration(
        RenameMethodRector::class,
        [
            new MethodCallRename(
                'OxidEsales\Facts\Facts',
                'getShopUrl',
                'getShopBaseUrl'
            ),
        ]
    );

    $rectorConfig->ruleWithConfiguration(
        RenameClassConstFetchRector::class,
        [
            new RenameClassAndConstFetch(
                'OxidEsales\Facts\Edition\EditionSelector',
                'ENTERPRISE',
                'OxidEsales\EshopCommunity\Internal\Framework\Edition\Edition',
                'Enterprise'
            ),
            new RenameClassAndConstFetch(
                'OxidEsales\Facts\Edition\EditionSelector',
                'PROFESSIONAL',
                'OxidEsales\EshopCommunity\Internal\Framework\Edition\Edition',
                'Professional'
            ),
            new RenameClassAndConstFetch(
                'OxidEsales\Facts\Edition\EditionSelector',
                'COMMUNITY',
                'OxidEsales\EshopCommunity\Internal\Framework\Edition\Edition',
                'Community'
            ),
        ]
    );
};
