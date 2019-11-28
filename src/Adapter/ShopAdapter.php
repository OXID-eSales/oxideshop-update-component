<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Adapter;

use OxidEsales\Eshop\Application\Model\UserPayment;

class ShopAdapter implements ShopAdapterInterface
{
    public function getPaymentKey(): string
    {
        $userPayment = oxNew(UserPayment::class);
        return $userPayment->getPaymentKey();
    }
}
