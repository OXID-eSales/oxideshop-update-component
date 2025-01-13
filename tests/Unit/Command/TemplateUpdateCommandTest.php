<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Unit\Command\FileReader;

use Exception;
use OxidEsales\OxidEshopUpdateComponent\Command\TemplateUpdateCommand;
use OxidEsales\OxidEshopUpdateComponent\Template\Migration\TemplateFilterMigratorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class TemplateUpdateCommandTest extends TestCase
{
    private TemplateFilterMigratorInterface | MockObject $templateFilterMigrator;
    private LoggerInterface | MockObject $logger;
    private InputInterface | MockObject $input;
    private OutputInterface | MockObject $output;
    private TemplateUpdateCommand $command;
    private string $templateDirectory = '/path/to/templates';

    protected function setUp(): void
    {
        $this->templateFilterMigrator = $this->createMock(TemplateFilterMigratorInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->input = $this->createMock(InputInterface::class);
        $this->output = $this->createMock(OutputInterface::class);

        $this->command = new TemplateUpdateCommand(
            $this->templateFilterMigrator,
            $this->logger
        );
    }

    public function testExecuteSuccessfulMigration(): void
    {
        $this->input
            ->method('getArgument')
            ->with(TemplateUpdateCommand::TEMPLATES_DIRECTORY)
            ->willReturn($this->templateDirectory);

        $this->templateFilterMigrator
            ->expects($this->once())
            ->method('migrate')
            ->with($this->templateDirectory);

        $this->assertEquals(
            Command::SUCCESS,
            $this->command->run($this->input, $this->output)
        );
    }

    public function testExecuteFail(): void
    {
        $this->input
            ->method('getArgument')
            ->with(TemplateUpdateCommand::TEMPLATES_DIRECTORY)
            ->willReturn($this->templateDirectory);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with(
                $this->stringContains('Unexpected error'),
                $this->arrayHasKey('exception')
            );

        $this->templateFilterMigrator
            ->method('migrate')
            ->willThrowException(new Exception());

        $this->assertEquals(
            Command::FAILURE,
            $this->command->run($this->input, $this->output)
        );
    }
}
