<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Decoder\DataMigration;

use OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionProviderInterface;
use OxidEsales\OxidEshopUpdateComponent\Decoder\Exception\WrongColumnType;

class Validator implements ValidatorInterface
{
    /**
     * @var ConnectionProviderInterface
     */
    private $connectionProvider;

    public function __construct(ConnectionProviderInterface $connectionProvider)
    {
        $this->connectionProvider = $connectionProvider;
    }

    /**
     * @param string $table
     * @param string $column
     * @throws WrongColumnType
     */
    public function validateIfAlreadyMigrated(string $table, string $column): void
    {
        $connection = $this->connectionProvider->get();
        $columnType = $connection
            ->fetchColumn(
                'SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS '
                . "WHERE table_name = '$table' AND COLUMN_NAME = '$column';"
            );
        if ($columnType === 'text') {
            throw new WrongColumnType("$table table is already in text type. Maybe migration already ran?");
        }
    }
}
