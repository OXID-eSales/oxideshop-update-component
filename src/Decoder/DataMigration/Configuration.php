<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Decoder\DataMigration;

use Doctrine\DBAL\Connection;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\ContextInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(
        ContextInterface   $context,
        Connection         $connection,
        ValidatorInterface $validator
    ) {
        $this->context = $context;
        $this->connection = $connection;
        $this->validator = $validator;
    }

    public function migrate(): void
    {
        $this->validator->validateIfAlreadyMigrated('oxconfig', 'OXVARVALUE_TEXT');

        $this->connection->executeQuery('ALTER TABLE oxconfig ADD COLUMN OXVARVALUE_TEXT TEXT NOT NULL');
        $this->connection->executeQuery(
            'UPDATE oxconfig SET OXVARVALUE_TEXT = CONVERT(DECODE(oxvarvalue, ?), CHAR)',
            [
                $this->context->getConfigurationEncryptionKey()
            ]
        );
        $this->connection->executeQuery(
            'ALTER TABLE oxconfig DROP COLUMN OXVARVALUE'
        );
        $this->connection->executeQuery(
            'ALTER TABLE oxconfig CHANGE '
            . 'COLUMN OXVARVALUE_TEXT OXVARVALUE text NOT NULL COMMENT \'Variable value\' AFTER OXVARTYPE'
        );
    }
}
