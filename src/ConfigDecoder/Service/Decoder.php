<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\ConfigDecoder\Service;

use OxidEsales\EshopCommunity\Internal\Framework\Config\Dao\ShopConfigurationSettingDaoInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;

/**
 * @internal
 */
class Decoder implements DecoderInterface
{
    /**
     * @var ShopConfigurationSettingDaoInterface
     */
    private $settingDao;
    /**
     * @var QueryBuilderFactoryInterface
     */
    private $queryBuilderFactory;

    public function __construct(
        QueryBuilderFactoryInterface $queryBuilderFactory
    ) {
        $this->queryBuilderFactory = $queryBuilderFactory;
    }

    public function decode(): void
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder
            ->select('oxvarname')
            ->from('oxconfig')
            ->andWhere('oxvarname = :name')
            ->andWhere('oxmodule = ""');

        $result = $queryBuilder->execute()->fetch();
    }
}
