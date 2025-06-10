<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\DatabaseSchema\Updater;

use OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionFactoryInterface;

readonly class DropColumnsFromOxarticlesUpdater implements DatabaseUpdaterInterface
{
    public function __construct(private ConnectionFactoryInterface $connectionFactory)
    {
    }

    public function update(): void
    {
        $connection = $this->connectionFactory->create();
        $columns = $connection->fetchFirstColumn('SHOW COLUMNS FROM oxarticles');

        $dropColumns = [
            'OXPIC1', 'OXPIC2', 'OXPIC3', 'OXPIC4',
            'OXPIC5', 'OXPIC6', 'OXPIC7', 'OXPIC8',
            'OXPIC9', 'OXPIC10', 'OXPIC11', 'OXPIC12',
            'OXTHUMB', 'OXICON'
        ];

        $columnsToDrop = array_intersect($dropColumns, $columns);

        if (!empty($columnsToDrop)) {
            $columnsSql = implode(", DROP COLUMN ", $columnsToDrop);
            $connection->executeStatement(sprintf('ALTER TABLE oxarticles DROP COLUMN %s', $columnsSql));
        }
    }
}
