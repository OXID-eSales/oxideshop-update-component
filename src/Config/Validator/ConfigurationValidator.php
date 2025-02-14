<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Config\Validator;

use OxidEsales\OxidEshopUpdateComponent\Config\ConfigurationPathProvider;
use OxidEsales\OxidEshopUpdateComponent\Config\Exception\ConfigurationFileNotFoundException;

class ConfigurationValidator implements ConfigurationValidatorInterface
{
    public function __construct(
        private readonly ConfigurationPathProvider $pathProvider
    ) {
    }

    public function validate(): void
    {
        if (!file_exists($this->pathProvider->getConfigurationFilePath())) {
            throw new ConfigurationFileNotFoundException('Configuration file config.inc.php not found');
        }
    }
}
