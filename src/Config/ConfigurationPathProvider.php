<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Config;

use OxidEsales\EshopCommunity\Internal\Transition\Utility\BasicContextInterface;

class ConfigurationPathProvider
{
    public function __construct(
        private readonly BasicContextInterface $context
    ) {
    }

    public function getConfigurationFilePath(): string
    {
        return $this->context->getSourcePath() . '/config.inc.php';
    }

    public function getConfigurationBackupPath(): string
    {
        return $this->getConfigurationFilePath() . '.bak';
    }
}