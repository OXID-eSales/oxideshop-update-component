<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Integration\DatabaseSchema\Updater;

use OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionFactoryInterface;
use OxidEsales\EshopCommunity\Tests\ContainerTrait;
use OxidEsales\EshopCommunity\Tests\DatabaseTrait;
use OxidEsales\OxidEshopUpdateComponent\DatabaseSchema\Updater\DropColumnsFromOxarticlesUpdater;
use PHPUnit\Framework\TestCase;

final class DropColumnsFromOxarticlesUpdaterTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTrait;

    public function tearDown(): void
    {
        $this->setupShopDatabase();

        parent::tearDown();
    }

    public function testUpdate(): void
    {
        $this->get(DropColumnsFromOxarticlesUpdater::class)->update();

        $this->assertPicturesColumnsAreNotExistInOxarticlesTable();
    }

    private function assertPicturesColumnsAreNotExistInOxarticlesTable(): void
    {
        $columns = $this->get(ConnectionFactoryInterface::class)
            ->create()
            ->fetchFirstColumn('SHOW COLUMNS FROM oxarticles');

        $dropColumns = [
            'OXPIC1', 'OXPIC2', 'OXPIC3', 'OXPIC4',
            'OXPIC5', 'OXPIC6', 'OXPIC7', 'OXPIC8',
            'OXPIC9', 'OXPIC10', 'OXPIC11', 'OXPIC12',
            'OXTHUMB', 'OXICON'
        ];

        $this->assertEmpty(array_intersect($dropColumns, $columns));
    }
}
