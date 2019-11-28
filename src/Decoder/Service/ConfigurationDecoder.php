<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Decoder\Service;

use OxidEsales\OxidEshopUpdateComponent\Decoder\Dao\ConfigurationDaoInterface;

class ConfigurationDecoder implements DecoderInterface
{
    /**
     * @var ConfigurationDaoInterface
     */
    private $configurationDao;

    public function __construct(
        ConfigurationDaoInterface $configurationDao
    ) {
        $this->configurationDao = $configurationDao;
    }

    public function decode(): void
    {
        $this->configurationDao->decodeValues();
    }
}
