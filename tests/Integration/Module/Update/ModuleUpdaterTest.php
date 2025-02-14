<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Integration\Module\Update;

use OxidEsales\OxidEshopUpdateComponent\Module\Configuration\ModuleRefactorConfiguration;
use OxidEsales\OxidEshopUpdateComponent\Module\Exception\ModuleNotFoundException;
use OxidEsales\OxidEshopUpdateComponent\Module\Update\ModuleUpdater;
use OxidEsales\OxidEshopUpdateComponent\Module\Factory\RectorConfigFactory;
use OxidEsales\OxidEshopUpdateComponent\Module\Process\ProcessRunnerInterface;
use OxidEsales\OxidEshopUpdateComponent\Module\Validator\ModuleValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;

final class ModuleUpdaterTest extends TestCase
{
    private ModuleUpdater $moduleUpdater;
    /** @var ProcessRunnerInterface&MockObject */
    private ProcessRunnerInterface $processRunner;
    private string $testModulePath;

    protected function setUp(): void
    {
        $this->setUpVirtualFileSystem();
        $this->setUpModuleUpdater();
    }

    private function setUpVirtualFileSystem(): void
    {
        vfsStream::setup('root', null, ['test-module' => []]);
        $this->testModulePath = vfsStream::url('root/test-module');
    }

    private function setUpModuleUpdater(): void
    {
        $this->processRunner = $this->createMock(ProcessRunnerInterface::class);
        $this->moduleUpdater = new ModuleUpdater(
            new ModuleValidator(),
            $this->processRunner,
            new RectorConfigFactory()
        );
    }

    public function testSuccessfulModuleUpdate(): void
    {
        $configuration = new ModuleRefactorConfiguration(
            importNames: false,
            typeCoverageLevel: 0,
            deadCodeLevel: 0,
            codeQualityLevel: 0,
            preparedSets: [],
            customSets: ['config' => true]
        );

        $this->processRunner
            ->expects($this->once())
            ->method('run');

        $this->moduleUpdater->update($this->testModulePath, $configuration);
    }

    public function testModuleValidationFailure(): void
    {
        $invalidModulePath = vfsStream::url('root/nonexistent-module');
        $configuration = new ModuleRefactorConfiguration(
            importNames: true,
            typeCoverageLevel: 5,
            deadCodeLevel: 5,
            codeQualityLevel: 5,
            preparedSets: [],
            customSets: ['config' => true]
        );

        $this->expectException(ModuleNotFoundException::class);
        $this->moduleUpdater->update($invalidModulePath, $configuration);
    }
}
