<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Rector;

use Iterator;
use OxidEsales\OxidEshopUpdateComponent\Module\Configuration\ModuleRefactorConfiguration;
use OxidEsales\OxidEshopUpdateComponent\Module\Factory\RectorConfigFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RectorTest extends AbstractRectorTestCase
{
    private static string $tempConfigFile;

    protected function tearDown(): void
    {
        parent::tearDown();
        if (isset(self::$tempConfigFile) && file_exists(self::$tempConfigFile)) {
            unlink(self::$tempConfigFile);
        }
    }

    #[DataProvider('provideData')]
    public function test(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    public static function provideData(): Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    public function provideConfigFilePath(): string
    {
        $configuration = new ModuleRefactorConfiguration(
            importNames: false,
            typeCoverageLevel: 0,
            deadCodeLevel: 0,
            codeQualityLevel: 0,
            preparedSets: [],
            customSets: [
                'config' => true,
                'facts' => true,
                'transaction' => true,
                'database' => true,
                'container' => true
            ]
        );

        $factory = new RectorConfigFactory();
        self::$tempConfigFile = $factory->createConfigurationFile($configuration, __DIR__);
        return self::$tempConfigFile;
    }
}
