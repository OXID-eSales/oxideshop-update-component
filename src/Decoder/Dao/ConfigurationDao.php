<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Decoder\Dao;

use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Database\TransactionServiceInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\ContextInterface;

class ConfigurationDao implements ConfigurationDaoInterface
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
     * @var ContextInterface
     */
    private $context;

    public function __construct(
        TransactionServiceInterface $transactionService,
        QueryBuilderFactoryInterface $queryBuilderFactory,
        ContextInterface $context
    ) {
        $this->transactionService = $transactionService;
        $this->queryBuilderFactory = $queryBuilderFactory;
        $this->context = $context;
    }

    public function decodeValues(): void
    {
        try {
            $this->transactionService->begin();
            $queryBuilder = $this->queryBuilderFactory->create();
            $queryBuilder
                ->select(['oxid', 'decode(oxvarvalue, :key) as value'])
                ->from('oxconfig')
                ->setParameters([
                    'key' => $this->context->getConfigurationEncryptionKey(),
                ]);

            $results = $queryBuilder->execute()->fetchAll();

            foreach ($results as $config) {
                $queryBuilder->update('oxconfig')
                    ->set('oxvarvalue', $queryBuilder->expr()->literal($config['value']))
                    ->where('oxid = :id')
                    ->setParameter('id', $config['oxid'])->execute();
            }

            $this->transactionService->commit();
        } catch (\Throwable $throwable) {
            $this->transactionService->rollback();
            throw $throwable;
        }
    }
}
