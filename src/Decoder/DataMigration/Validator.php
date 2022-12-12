<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Decoder\DataMigration;

use Doctrine\DBAL\Connection;
use OxidEsales\OxidEshopUpdateComponent\Decoder\Exception\WrongColumnType;

class Validator implements ValidatorInterface
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $table
     * @param string $column
     * @throws WrongColumnType
     */
    public function validateIfAlreadyMigrated(string $table, string $column): void
    {
        $connection = $this->connection;
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
