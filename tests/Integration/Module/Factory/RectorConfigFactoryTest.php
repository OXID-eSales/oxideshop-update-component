<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Integration\Module\Factory;

use OxidEsales\OxidEshopUpdateComponent\Module\Configuration\ModuleRefactorConfiguration;
use OxidEsales\OxidEshopUpdateComponent\Module\Factory\RectorConfigFactory;
use PHPUnit\Framework\TestCase;

class RectorConfigFactoryTest extends TestCase
{
    private RectorConfigFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new RectorConfigFactory();
    }

    public function testCreateConfigurationFile(): void
    {
        $configuration = new ModuleRefactorConfiguration(
            importNames: true,
            typeCoverageLevel: 5,
            deadCodeLevel: 3,
            codeQualityLevel: 4,
            preparedSets: ['deadCode', 'codeQuality'],
            customSets: ['config' => true, 'facts' => false]
        );

        $configFile = $this->factory->createConfigurationFile($configuration, '/test/path');

        $this->assertFileExists($configFile);
        $content = file_get_contents($configFile);

        $this->assertStringContainsString("->withTypeCoverageLevel(5)", $content);
        $this->assertStringContainsString("->withDeadCodeLevel(3)", $content);
        $this->assertStringContainsString("->withCodeQualityLevel(4)", $content);
        $this->assertStringContainsString("deadCode: true", $content);
        $this->assertStringContainsString("codeQuality: true", $content);
        $this->assertStringContainsString("->withImportNames(removeUnusedImports: true)", $content);
        $this->assertStringContainsString("config.php", $content);
        $this->assertStringNotContainsString("facts.php", $content);
    }

    public function testCreateConfigurationWithoutCustomSets(): void
    {
        $configuration = new ModuleRefactorConfiguration(
            importNames: false,
            typeCoverageLevel: 0,
            deadCodeLevel: 0,
            codeQualityLevel: 0,
            preparedSets: [],
            customSets: []
        );

        $configFile = $this->factory->createConfigurationFile($configuration, '/test/path');

        $content = file_get_contents($configFile);
        $this->assertStringNotContainsString("->withSets", $content);
    }
}
