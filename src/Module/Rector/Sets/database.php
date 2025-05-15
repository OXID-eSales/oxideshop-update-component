<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\OxidEshopUpdateComponent\Module\Rector\Rules\DatabaseNumericFieldAccessToStringKeyRule;
use OxidEsales\OxidEshopUpdateComponent\Module\Rector\Rules\DatabaseParameterPrefixRule;
use OxidEsales\OxidEshopUpdateComponent\Module\Rector\Rules\DatabaseRemoveSetFetchModeRule;
use OxidEsales\OxidEshopUpdateComponent\Module\Rector\Rules\DatabaseRowNumericIndexAccessToArrayValuesRule;
use OxidEsales\OxidEshopUpdateComponent\Module\Rector\Rules\QueryBuilderRule;
use Rector\Arguments\Rector\MethodCall\RemoveMethodCallParamRector;
use Rector\Arguments\ValueObject\RemoveMethodCallParam;
use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(DoctrineSetList::DOCTRINE_DBAL_211);
    $rectorConfig->import(DoctrineSetList::DOCTRINE_DBAL_30);
    $rectorConfig->import(DoctrineSetList::DOCTRINE_DBAL_40);

    $rectorConfig->rule(DatabaseParameterPrefixRule::class);
    $rectorConfig->rule(DatabaseRemoveSetFetchModeRule::class);
    $rectorConfig->rule(DatabaseNumericFieldAccessToStringKeyRule::class);
    $rectorConfig->rule(DatabaseRowNumericIndexAccessToArrayValuesRule::class);
    $rectorConfig->rule(QueryBuilderRule::class);

    $rectorConfig->ruleWithConfiguration(
        RemoveMethodCallParamRector::class,
        [
            new RemoveMethodCallParam(DatabaseProvider::class, 'getDb', 0),
            new RemoveMethodCallParam(DatabaseProvider::class, 'getMaster', 0),
        ]
    );
};
