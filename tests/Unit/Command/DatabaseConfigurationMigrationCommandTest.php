<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Unit\Command;

use Exception;
use OxidEsales\OxidEshopUpdateComponent\Command\DatabaseConfigurationMigrationCommand;
use OxidEsales\OxidEshopUpdateComponent\DatabaseConfig\Migrator\DatabaseMigrationFactory;
use OxidEsales\OxidEshopUpdateComponent\DatabaseConfig\Migrator\DatabaseToContainerConfigurationMigratorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class DatabaseConfigurationMigrationCommandTest extends TestCase
{
    private DatabaseMigrationFactory|MockObject $migrationFactory;
    private InputInterface|MockObject $input;
    private OutputInterface|MockObject $output;

    protected function setUp(): void
    {
        $this->migrationFactory = $this->createMock(DatabaseMigrationFactory::class);
        $this->input = $this->createMock(InputInterface::class);
        $this->output = $this->createMock(OutputInterface::class);
    }

    public function testExecuteSuccess(): void
    {
        $command = new DatabaseConfigurationMigrationCommand($this->migrationFactory);

        $this->input->method('getArgument')
            ->with('remove-old-configuration')
            ->willReturn(false);

        $migrationService = $this->createMock(DatabaseToContainerConfigurationMigratorInterface::class);
        $migrationService->expects($this->once())
            ->method('migrateDatabaseToContainerConfiguration');

        $this->migrationFactory->expects($this->once())
            ->method('createMigrationService')
            ->with(false)
            ->willReturn($migrationService);

        $this->assertEquals(Command::SUCCESS, $command->run($this->input, $this->output));
    }

    public function testExecuteFailure(): void
    {
        $exceptionMessage = 'test-exception-message';
        $output = new BufferedOutput();
        $command = new DatabaseConfigurationMigrationCommand($this->migrationFactory);

        $this->input->method('getArgument')
            ->with('remove-old-configuration')
            ->willReturn(true);

        $migrationService = $this->createMock(DatabaseToContainerConfigurationMigratorInterface::class);
        $migrationService->expects($this->once())
            ->method('migrateDatabaseToContainerConfiguration')
            ->willThrowException(new Exception($exceptionMessage));

        $this->migrationFactory->expects($this->once())
            ->method('createMigrationService')
            ->with(true)
            ->willReturn($migrationService);

        $this->assertEquals(Command::FAILURE, $command->run($this->input, $output));

        $this->assertStringContainsString(
            $exceptionMessage,
            $output->fetch()
        );
    }
}
