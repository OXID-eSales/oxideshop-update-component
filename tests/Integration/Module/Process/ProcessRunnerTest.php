<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Integration\Module\Process;

use OxidEsales\OxidEshopUpdateComponent\Module\Exception\ModuleUpdateException;
use OxidEsales\OxidEshopUpdateComponent\Module\Process\ProcessRunner;
use PHPUnit\Framework\TestCase;

class ProcessRunnerTest extends TestCase
{
    private ProcessRunner $processRunner;

    protected function setUp(): void
    {
        $this->processRunner = new ProcessRunner();
    }

    public function testSuccessfulCommand(): void
    {
        $this->processRunner->run(['echo', 'test']);
        $this->assertTrue(true, 'Process should run without exceptions');
    }

    public function testFailingCommand(): void
    {
        $this->expectException(ModuleUpdateException::class);
        $this->processRunner->run(['non_existent_command']);
    }
}
