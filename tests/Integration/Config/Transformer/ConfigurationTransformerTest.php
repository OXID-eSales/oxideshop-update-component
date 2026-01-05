<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Integration\Config\Transformer;

use OxidEsales\OxidEshopUpdateComponent\Config\Transformer\ConfigurationTransformer;
use PHPUnit\Framework\TestCase;

class ConfigurationTransformerTest extends TestCase
{
    private ConfigurationTransformer $transformer;

    protected function setUp(): void
    {
        $this->transformer = new ConfigurationTransformer();
    }

    public function testTransformToEnvConfig(): void
    {
        $config = [
            'sLogLevel' => 'debug',
            'iDebug' => 4,
            'sCompileDir' => '/tmp/compile',
            'sShopURL' => 'http://shop.example.com',
            'dbHost' => 'localhost',
            'dbUser' => 'user',
            'dbPwd' => 'pass',
            'dbName' => 'oxid',
            'dbPort' => '3307'
        ];

        $result = $this->transformer->transformToEnvConfig($config);

        $this->assertEquals('debug', $result['OXID_LOG_LEVEL']);
        $this->assertTrue($result['OXID_DEBUG_MODE']);
        $this->assertEquals('/tmp/compile', $result['OXID_BUILD_DIRECTORY']);
        $this->assertEquals('http://shop.example.com', $result['OXID_SHOP_BASE_URL']);
        $this->assertStringContainsString(
            'mysql://user:pass@localhost:3307/oxid',
            $result['OXID_DB_URL']
        );
        $this->assertEquals('prod', $result['OXID_ENV']);
        $this->assertArrayHasKey('OXID_DEFAULT_TIMEZONE', $result);
    }

    public function testTransformToParameterConfig(): void
    {
        $config = [
            'sAdminSSLURL' => 'https://admin.example.com',
            'sSSLAltImageUrl' => 'https://img.example.com',
            'blSeoLogging' => true,
            'aAllowedUploadTypes' => ['jpg', 'png'],
            'blForceSessionStart' => false
        ];

        $result = $this->transformer->transformToParameterConfig($config);

        $this->assertEquals('https://admin.example.com', $result['oxid_esales.shop_admin_url']);
        $this->assertEquals('https://img.example.com', $result['oxid_esales.alternative_image_url']);
        $this->assertTrue($result['oxid_esales.log_not_seo_urls']);
        $this->assertEquals(['jpg', 'png'], $result['oxid_esales.allowed_uploaded_types']);
        $this->assertArrayNotHasKey('oxid_esales.force_session_start', $result);
    }

    public function testEmptyArrayValuesAreNotTransformed(): void
    {
        $config = [
            'aAllowedUploadTypes' => [],
            'aMultiLangTables' => ['table1', 'table2']
        ];

        $result = $this->transformer->transformToParameterConfig($config);

        $this->assertArrayNotHasKey('oxid_esales.allowed_uploaded_types', $result);
        $this->assertArrayHasKey('oxid_esales.multilingual_tables', $result);
        $this->assertEquals(['table1', 'table2'], $result['oxid_esales.multilingual_tables']);
    }
}
