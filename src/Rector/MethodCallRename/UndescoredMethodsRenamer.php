<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Rector\MethodCallRename;

final class UndescoredMethodsRenamer
{
    private string $configFile = __DIR__ . '/config/oxid_v7_deprecated_underscored_classes_methods.csv';

    public function getMethodCallRenamesForModule(string $modulePath): array
    {
        return (new MethodCallRenameGenerator())
            ->getMethodCallRenamesForModule(
                $this->getMethodRenameConfiguration(),
                $modulePath,
            );
    }

    private function getMethodRenameConfiguration(): array
    {
        return array_map('str_getcsv', file($this->configFile));
    }
}
