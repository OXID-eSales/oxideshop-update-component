<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Decoder\Service;

use OxidEsales\OxidEshopUpdateComponent\Decoder\DataMigration\UserPaymentInterface;

class UserPaymentsDecoder implements DecoderInterface
{
    /**
     * @var UserPaymentInterface
     */
    private $userPaymentMigration;

    public function __construct(
        UserPaymentInterface $userPaymentMigration
    ) {
        $this->userPaymentMigration = $userPaymentMigration;
    }

    public function decode(): void
    {
        $this->userPaymentMigration->migrate();
    }
}
