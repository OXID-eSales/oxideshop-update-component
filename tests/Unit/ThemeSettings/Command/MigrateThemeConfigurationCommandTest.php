<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Unit\ThemeSettings\Command;

use OxidEsales\OxidEshopUpdateComponent\Command\MigrateThemeConfigurationCommand;
use OxidEsales\OxidEshopUpdateComponent\ThemeSettings\Migration\ThemeSettingsMigrationResult;
use OxidEsales\OxidEshopUpdateComponent\ThemeSettings\Migration\ThemeSettingsMigratorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class MigrateThemeConfigurationCommandTest extends TestCase
{
    public function testWarnsAboutThemesWithoutConfiguration(): void
    {
        $migrator = $this->createStub(ThemeSettingsMigratorInterface::class);
        $migrator->method('migrate')->willReturn(new ThemeSettingsMigrationResult(['partnerTheme', 'anotherTheme']));

        $tester = new CommandTester(new MigrateThemeConfigurationCommand($migrator));
        $tester->execute([]);

        $display = $tester->getDisplay();
        $this->assertStringContainsString('partnerTheme', $display);
        $this->assertStringContainsString('anotherTheme', $display);
    }

    public function testReportsSuccessWithoutWarningWhenAllThemesMigrated(): void
    {
        $migrator = $this->createStub(ThemeSettingsMigratorInterface::class);
        $migrator->method('migrate')->willReturn(new ThemeSettingsMigrationResult([]));

        $tester = new CommandTester(new MigrateThemeConfigurationCommand($migrator));
        $tester->execute([]);

        $this->assertStringContainsString('successfully migrated', $tester->getDisplay());
    }
}
