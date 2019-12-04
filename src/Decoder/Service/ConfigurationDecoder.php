<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Decoder\Service;

use OxidEsales\OxidEshopUpdateComponent\Decoder\DataMigration\ConfigurationInterface;

class ConfigurationDecoder implements DecoderInterface
{
    /**
     * @var ConfigurationInterface
     */
    private $configurationMigration;

    public function __construct(
        ConfigurationInterface $configurationMigration
    ) {
        $this->configurationMigration = $configurationMigration;
    }

    public function decode(): void
    {
        $this->configurationMigration->migrate();
    }
}
