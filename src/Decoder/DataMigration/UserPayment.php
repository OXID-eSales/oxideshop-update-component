<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Decoder\DataMigration;

use Doctrine\DBAL\Connection;
use OxidEsales\OxidEshopUpdateComponent\Adapter\ShopAdapterInterface;
use OxidEsales\OxidEshopUpdateComponent\Decoder\Exception\WrongColumnType;

class UserPayment implements UserPaymentInterface
{
    /**
     * @var ShopAdapterInterface
     */
    private $shopAdapter;

    /**
     * @var Connection
     */
    private $connection;
    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(
        ShopAdapterInterface $shopAdapter,
        Connection $connection,
        ValidatorInterface $validator
    ) {
        $this->shopAdapter = $shopAdapter;
        $this->connection = $connection;
        $this->validator = $validator;
    }

    public function migrate(): void
    {
        $this->validator->validateIfAlreadyMigrated('oxuserpayments', 'OXVALUE');

        $paymentKey = $this->shopAdapter->getPaymentKey();
        $this->connection->executeQuery('ALTER TABLE oxuserpayments ADD COLUMN OXVALUE_TEXT TEXT NOT NULL');
        $this->connection->executeQuery(
            'UPDATE oxuserpayments SET OXVALUE_TEXT = CONVERT(DECODE(OXVALUE, ?), CHAR)',
            [
                $paymentKey
            ]
        );
        $this->connection->executeQuery(
            'ALTER TABLE oxuserpayments DROP COLUMN OXVALUE'
        );
        $this->connection->executeQuery(
            'ALTER TABLE oxuserpayments '
            . 'CHANGE COLUMN OXVALUE_TEXT OXVALUE text NOT NULL COMMENT \'DYN payment values array as string\' '
            . 'AFTER OXPAYMENTSID'
        );
    }
}
