<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Unit\DatabaseConfig\Fetcher;

use OxidEsales\EshopCommunity\Internal\Framework\Config\Dao\ShopConfigurationSettingDaoInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Config\DataObject\ShopConfigurationSetting;
use OxidEsales\EshopCommunity\Internal\Framework\Dao\EntryDoesNotExistDaoException;
use OxidEsales\OxidEshopUpdateComponent\DatabaseConfig\Fetcher\DatabaseConfigurationFetcher;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class DatabaseConfigurationFetcherTest extends TestCase
{
    private int $shopId = 123;
    private MockObject|ShopConfigurationSettingDaoInterface $databaseConfigurationSettingDaoMock;
    private DatabaseConfigurationFetcher $databaseConfigurationFetcher;

    protected function setUp(): void
    {
        $this->databaseConfigurationSettingDaoMock = $this->createMock(
            ShopConfigurationSettingDaoInterface::class
        );

        $this->databaseConfigurationFetcher = new DatabaseConfigurationFetcher(
            $this->databaseConfigurationSettingDaoMock
        );
    }

    public function testFetchDatabaseConfigurationValuesWhenSettingExists(): void
    {
        $databaseParameterNames = ['param1', 'param2'];
        $settingMock1 = $this->createMock(ShopConfigurationSetting::class);
        $settingMock1->method('getValue')->willReturn('value1');

        $settingMock2 = $this->createMock(ShopConfigurationSetting::class);
        $settingMock2->method('getValue')->willReturn('value2');

        $this->databaseConfigurationSettingDaoMock
            ->method('get')
            ->willReturnMap([
                ['param1', $this->shopId, $settingMock1],
                ['param2', $this->shopId, $settingMock2]
            ]);

        $result = $this->databaseConfigurationFetcher->fetchDatabaseConfigurationValues(
            $this->shopId,
            $databaseParameterNames
        );

        $this->assertArrayHasKey('param1', $result);
        $this->assertArrayHasKey('param2', $result);
        $this->assertEquals('value1', $result['param1']);
        $this->assertEquals('value2', $result['param2']);
    }

    public function testFetchDatabaseConfigurationValuesWhenSettingDoesNotExist(): void
    {
        $databaseParameterNames = ['param1', 'param2'];

        $this->databaseConfigurationSettingDaoMock
            ->method('get')
            ->willThrowException(new EntryDoesNotExistDaoException());

        $result = $this->databaseConfigurationFetcher->fetchDatabaseConfigurationValues(
            $this->shopId,
            $databaseParameterNames
        );

        $this->assertEmpty($result);
    }

    public function testFetchDatabaseConfigurationValuesWithInvalidParameter(): void
    {
        $databaseParameterNames = ['invalidParam'];

        $this->databaseConfigurationSettingDaoMock
            ->method('get')
            ->willThrowException(new EntryDoesNotExistDaoException());

        $result = $this->databaseConfigurationFetcher->fetchDatabaseConfigurationValues(
            $this->shopId,
            $databaseParameterNames
        );

        $this->assertEmpty($result);
    }
}
