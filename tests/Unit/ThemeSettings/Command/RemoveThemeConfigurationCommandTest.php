<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Unit\ThemeSettings\Command;

use OxidEsales\OxidEshopUpdateComponent\Command\RemoveThemeConfigurationCommand;
use OxidEsales\OxidEshopUpdateComponent\ThemeSettings\Remover\ThemeConfigurationRemoverInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class RemoveThemeConfigurationCommandTest extends TestCase
{
    public function testAbortsWithoutRemovingWhenNotConfirmed(): void
    {
        $remover = $this->createMock(ThemeConfigurationRemoverInterface::class);
        $remover->expects($this->never())->method('remove');

        $tester = new CommandTester(new RemoveThemeConfigurationCommand($remover));
        $tester->setInputs(['no']);
        $tester->execute([]);

        $this->assertStringContainsString('Aborted', $tester->getDisplay());
    }

    public function testRemovesWhenConfirmed(): void
    {
        $remover = $this->createMock(ThemeConfigurationRemoverInterface::class);
        $remover->expects($this->once())->method('remove');

        $tester = new CommandTester(new RemoveThemeConfigurationCommand($remover));
        $tester->setInputs(['yes']);
        $tester->execute([]);
    }

    public function testRemovesWithoutPromptWhenForced(): void
    {
        $remover = $this->createMock(ThemeConfigurationRemoverInterface::class);
        $remover->expects($this->once())->method('remove');

        $tester = new CommandTester(new RemoveThemeConfigurationCommand($remover));
        $tester->execute(['--force' => true]);
    }
}
