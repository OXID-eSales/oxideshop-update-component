<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Decoder\DataMigration;

use OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionProviderInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\ContextInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @var ConnectionProviderInterface
     */
    private $connectionProvider;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(
        ContextInterface $context,
        ConnectionProviderInterface $connectionProvider,
        ValidatorInterface $validator
    ) {
        $this->context = $context;
        $this->connectionProvider = $connectionProvider;
        $this->validator = $validator;
    }

    public function migrate(): void
    {
        $this->validator->validateIfAlreadyMigrated('oxconfig', 'OXVARVALUE_TEXT');

        $connection = $this->connectionProvider->get();

        $connection->executeQuery('ALTER TABLE oxconfig ADD COLUMN OXVARVALUE_TEXT TEXT NOT NULL');
        $connection->executeQuery(
            'UPDATE oxconfig SET OXVARVALUE_TEXT = CONVERT(DECODE(oxvarvalue, ?), CHAR)',
            [
                $this->context->getConfigurationEncryptionKey()
            ]
        );
        $connection->executeQuery(
            'ALTER TABLE oxconfig DROP COLUMN OXVARVALUE'
        );
        $connection->executeQuery(
            'ALTER TABLE oxconfig CHANGE '
            . 'COLUMN OXVARVALUE_TEXT OXVARVALUE text NOT NULL COMMENT \'Variable value\' AFTER OXVARTYPE'
        );
    }
}
