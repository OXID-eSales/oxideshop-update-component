<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Unit\SslUrl\Command;

use OxidEsales\OxidEshopUpdateComponent\Command\MigrateSslUrlConfigurationCommand;
use OxidEsales\OxidEshopUpdateComponent\SslUrl\Migration\SslUrlConfigurationMigratorInterface;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class MigrateSslUrlConfigurationCommandTest extends TestCase
{
    public function testMigratesWhenConfirmed(): void
    {
        $migrator = $this->createMock(SslUrlConfigurationMigratorInterface::class);
        $migrator->expects($this->once())->method('migrate');

        $tester = new CommandTester(new MigrateSslUrlConfigurationCommand($migrator));
        $tester->setInputs(['yes']);
        $tester->execute([]);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
    }

    public function testReturnsFailureWhenMigrationFails(): void
    {
        $migrator = $this->createStub(SslUrlConfigurationMigratorInterface::class);
        $migrator->method('migrate')->willThrowException(new RuntimeException('Database error'));

        $tester = new CommandTester(new MigrateSslUrlConfigurationCommand($migrator));
        $tester->setInputs(['yes']);
        $tester->execute([]);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString('Database error', $tester->getDisplay());
    }

    public function testAbortsWithoutMigratingWhenNotConfirmed(): void
    {
        $migrator = $this->createMock(SslUrlConfigurationMigratorInterface::class);
        $migrator->expects($this->never())->method('migrate');

        $tester = new CommandTester(new MigrateSslUrlConfigurationCommand($migrator));
        $tester->setInputs(['no']);
        $tester->execute([]);

        $this->assertStringContainsString('Aborted', $tester->getDisplay());
    }

    public function testAbortsInNonInteractiveModeWithoutForce(): void
    {
        $migrator = $this->createMock(SslUrlConfigurationMigratorInterface::class);
        $migrator->expects($this->never())->method('migrate');

        $tester = new CommandTester(new MigrateSslUrlConfigurationCommand($migrator));
        $tester->execute([], ['interactive' => false]);

        $this->assertStringContainsString('Aborted', $tester->getDisplay());
    }

    public function testMigratesWithoutPromptWhenForced(): void
    {
        $migrator = $this->createMock(SslUrlConfigurationMigratorInterface::class);
        $migrator->expects($this->once())->method('migrate');

        $tester = new CommandTester(new MigrateSslUrlConfigurationCommand($migrator));
        $tester->execute(['--force' => true]);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertStringNotContainsString('Do you want to continue?', $tester->getDisplay());
    }
}
