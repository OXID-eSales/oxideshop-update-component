<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\DatabaseConfig\ParametersProvider;

interface ConfigurationParametersProviderInterface
{
    public function getDatabaseConfigurationParameters(): array;

    public function getContainerParameterName(string $databaseConfigurationParameter): string;
}
