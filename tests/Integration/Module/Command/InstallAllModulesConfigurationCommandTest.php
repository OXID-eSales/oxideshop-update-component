<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Integration\Module\Command;

use Monolog\Logger;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\BasicContextInterface;
use OxidEsales\EshopCommunity\Internal\Framework\FileSystem\FinderFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Dao\ModuleConfigurationDaoInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Install\Service\ModuleConfigurationInstallerInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\ContextInterface;
use OxidEsales\EshopCommunity\Tests\Integration\Internal\ContainerTrait;
use OxidEsales\OxidEshopUpdateComponent\Module\Command\InstallAllModulesConfigurationCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
final class InstallAllModulesConfigurationCommandTest extends TestCase
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
     * @expectedException \OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Exception\ModuleConfigurationNotFoundException
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

        $command = new InstallAllModulesConfigurationCommand(
            $this->get(ModuleConfigurationInstallerInterface::class),
            $context,
            $this->get(FinderFactoryInterface::class),
            $this->getMockBuilder(Logger::class)->setMethods(['error'])->disableOriginalConstructor()->getMock()
        );
        $command->setName('oe:oxideshop-update-component:install-all-modules');

        $application->add($command);

        $command = $application->find($command->getName());
        $command->setApplication($application);
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => $command->getName()]);
    }
}
