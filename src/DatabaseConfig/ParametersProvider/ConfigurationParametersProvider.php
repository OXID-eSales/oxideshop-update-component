<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\DatabaseConfig\ParametersProvider;

class ConfigurationParametersProvider implements ConfigurationParametersProviderInterface
{
    private array $databaseToContainerParameterMap = [
        'blCacheActive' => 'oxid_esales.enable_data_cache',
        'blUseContentCaching' => 'oxid_esales.enable_content_cache',
    ];

    public function getDatabaseConfigurationParameters(): array
    {
        return array_keys($this->databaseToContainerParameterMap);
    }

    public function getContainerParameterName(string $databaseConfigurationParameter): string
    {
        return $this->databaseToContainerParameterMap[$databaseConfigurationParameter];
    }
}
