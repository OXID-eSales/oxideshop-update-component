<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Decoder\Service;

use OxidEsales\OxidEshopUpdateComponent\Decoder\Dao\UserPaymentDaoInterface;

/**
 * @internal
 */
class UserPaymentsDecoder implements DecoderInterface
{
    /**
     * @var UserPaymentDaoInterface
     */
    private $userPaymentDao;

    public function __construct(
        UserPaymentDaoInterface $userPaymentDao
    ) {
        $this->userPaymentDao = $userPaymentDao;
    }

    public function decode(): void
    {
        $this->userPaymentDao->decodeValues();
    }
}
