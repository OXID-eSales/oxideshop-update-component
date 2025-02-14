<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Config\Reader;

use OxidEsales\OxidEshopUpdateComponent\Config\ConfigurationPathProvider;

class ConfigurationReader implements ConfigurationReaderInterface
{
    public function __construct(
        private readonly ConfigurationPathProvider $pathProvider
    ) {
    }

    public function read(): array
    {
        $config = new \stdClass();
        $configPath = $this->pathProvider->getConfigurationFilePath();

        $include = function () use ($configPath) {
            require $configPath;
        };
        $include->bindTo($config)();

        return get_object_vars($config);
    }
}
