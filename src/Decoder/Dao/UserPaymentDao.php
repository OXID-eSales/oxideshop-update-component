<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Decoder\Dao;

use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Database\TransactionServiceInterface;
use OxidEsales\OxidEshopUpdateComponent\Adapter\ShopAdapterInterface;

/**
 * @internal
 */
class UserPaymentDao implements UserPaymentDaoInterface
{
    /**
     * @var TransactionServiceInterface
     */
    private $transactionService;

    /**
     * @var QueryBuilderFactoryInterface
     */
    private $queryBuilderFactory;
    /**
     * @var ShopAdapterInterface
     */
    private $shopAdapter;

    public function __construct(
        TransactionServiceInterface $transactionService,
        QueryBuilderFactoryInterface $queryBuilderFactory,
        ShopAdapterInterface $shopAdapter
    ) {
        $this->transactionService = $transactionService;
        $this->queryBuilderFactory = $queryBuilderFactory;
        $this->shopAdapter = $shopAdapter;
    }

    public function decodeValues(): void
    {
        try {
            $this->transactionService->begin();
            $paymentKey = $this->shopAdapter->getPaymentKey();
            $queryBuilder = $this->queryBuilderFactory->create();
            $queryBuilder
                ->select(['oxid', 'decode(oxvalue, :key) as value'])
                ->from('oxuserpayments')
                ->setParameters([
                    'key' => $paymentKey,
                ]);

            $results = $queryBuilder->execute()->fetchAll();

            foreach ($results as $userPayment) {
                $queryBuilder->update('oxuserpayments')
                    ->set('oxvalue', $queryBuilder->expr()->literal($userPayment['value']))
                    ->where('oxid = :id')
                    ->setParameter('id', $userPayment['oxid'])
                    ->execute();
            }

            $this->transactionService->commit();
        } catch (\Throwable $throwable) {
            $this->transactionService->rollback();
            throw $throwable;
        }
    }
}
