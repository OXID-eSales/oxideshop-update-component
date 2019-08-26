<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Integration\Module\Command;

use OxidEsales\EshopCommunity\Internal\Application\Utility\BasicContextInterface;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\Dao\ModuleConfigurationDaoInterface;
use OxidEsales\EshopCommunity\Internal\Module\Install\Service\ModuleConfigurationInstallerInterface;
use OxidEsales\EshopCommunity\Internal\Utility\ContextInterface;
use OxidEsales\EshopCommunity\Tests\Integration\Internal\ContainerTrait;
use OxidEsales\OxidEshopUpdateComponent\Module\Command\InstallAllModulesConfigurationCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * @internal
 */
class InstallAllModulesConfigurationCommandTest extends TestCase
{
    use ContainerTrait;

    private $shopId;
    private $testModule = 'testModule1';
    private $anotherTestModule = 'testModule2';

    /**
     * @var Filesystem
     */
    private $fileSystem;

    public function setUp(): void
    {
        $context = $this->get(ContextInterface::class);
        $this->fileSystem = new Filesystem();
        $this->shopId = $context->getCurrentShopId();

        parent::setUp();
    }

    public function tearDown(): void
    {
        $this->fileSystem->remove(__DIR__.'/Fixtures/noModules');
        parent::tearDown();
    }

    public function testInstallModulesWithMultipleModules(): void
    {
        $this->executeInstallAllModulesCommand(__DIR__.'/Fixtures/');

        $moduleConfiguration = $this->get(ModuleConfigurationDaoInterface::class)
            ->get($this->testModule, $this->shopId);

        $this->assertSame(
            $this->testModule,
            $moduleConfiguration->getId()
        );

        $moduleConfiguration = $this->get(ModuleConfigurationDaoInterface::class)
            ->get($this->anotherTestModule, $this->shopId);

        $this->assertSame(
            $this->anotherTestModule,
            $moduleConfiguration->getId()
        );
    }

    /**
     * @expectedException \OxidEsales\EshopCommunity\Internal\Adapter\Exception\ModuleConfigurationNotFoundException
     */
    public function testInstallWithNoModules(): void
    {
        $noModulesDirectory = __DIR__ . '/Fixtures/noModules';
        $this->fileSystem->mkdir($noModulesDirectory);

        $this->executeInstallAllModulesCommand($noModulesDirectory);

        $this->get(ModuleConfigurationDaoInterface::class)
            ->get($this->testModule, $this->shopId);
    }

    private function executeInstallAllModulesCommand(string $modulesPath) : void
    {
        $context = $this->getMockBuilder(BasicContextInterface::class)->getMock();
        $context->method('getModulesPath')->willReturn($modulesPath);
        $application = new Application();
        $application->add(new InstallAllModulesConfigurationCommand(
            $this->get(ModuleConfigurationInstallerInterface::class),
            $context,
            new Finder()
        ));

        $command = $application->find('oe:oxideshop-update-component:install-all-modules');
        $command->setApplication($application);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);
    }
}
