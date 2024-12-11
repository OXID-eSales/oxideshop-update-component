<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Integration\Config\Migration;

use OxidEsales\EshopCommunity\Internal\Transition\Utility\BasicContextInterface;
use OxidEsales\OxidEshopUpdateComponent\Config\Exception\ConfigurationFileNotFoundException;
use OxidEsales\OxidEshopUpdateComponent\Config\ConfigurationPathProvider;
use OxidEsales\OxidEshopUpdateComponent\Config\Migration\ConfigurationMigrator;
use OxidEsales\OxidEshopUpdateComponent\Config\Reader\ConfigurationReader;
use OxidEsales\OxidEshopUpdateComponent\Config\Transformer\ConfigurationTransformer;
use OxidEsales\OxidEshopUpdateComponent\Config\Validator\ConfigurationValidator;
use OxidEsales\OxidEshopUpdateComponent\Config\Writer\ConfigurationWriter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use org\bovigo\vfs\vfsStream;
use Symfony\Component\Yaml\Yaml;

class ConfigurationMigratorTest extends TestCase
{
    private $vfs;
    private ConfigurationMigrator $migrator;
    private BasicContextInterface $contextMock;
    private Filesystem $filesystem;
    private ConfigurationPathProvider $pathProvider;

    protected function setUp(): void
    {
        $this->initVirtualFileSystem();
        $this->initContext();
        $this->filesystem = new Filesystem();
        $this->pathProvider = new ConfigurationPathProvider($this->contextMock);
        $this->initMigrator();
    }

    private function initVirtualFileSystem(): void
    {
        $this->vfs = vfsStream::setup('root', null, [
            'source' => [
                'config.inc.php' => $this->getTestConfig()
            ],
            'shop' => [],
            'var' => [
                'configuration' => [
                    'shops' => []
                ]
            ]
        ]);
    }

    private function initContext(): void
    {
        $this->contextMock = $this->createMock(BasicContextInterface::class);
        $this->contextMock->method('getSourcePath')
            ->willReturn(vfsStream::url('root/source'));
        $this->contextMock->method('getShopRootPath')
            ->willReturn(vfsStream::url('root/shop'));
        $this->contextMock->method('getProjectConfigurationDirectory')
            ->willReturn(vfsStream::url('root/var/configuration/shops'));
    }

    private function initMigrator(): void
    {
        $validator = new ConfigurationValidator($this->pathProvider);
        $reader = new ConfigurationReader($this->pathProvider);
        $transformer = new ConfigurationTransformer();
        $writer = new ConfigurationWriter($this->contextMock, $this->filesystem);

        $this->migrator = new ConfigurationMigrator(
            $validator,
            $reader,
            $transformer,
            $writer,
            $this->pathProvider,
            $this->filesystem
        );
    }

    public function testSuccessfulMigration(): void
    {
        $this->migrator->migrate();

        $this->assertEnvFileContent();
        $this->assertParametersYamlContent();
        $this->assertBackupFileExists();
        $this->assertOriginalConfigRemoved();
    }

    private function assertEnvFileContent(): void
    {
        $envContent = file_get_contents(vfsStream::url('root/shop/.env'));
        $this->assertStringContainsString('OXID_DEBUG_MODE=true', $envContent);
        $this->assertStringContainsString('OXID_LOG_LEVEL=debug', $envContent);
        $this->assertStringContainsString('OXID_SHOP_BASE_URL=https://example.com', $envContent);
        $this->assertStringContainsString(
            'OXID_DB_URL=mysql://user:pass@localhost',
            $envContent
        );
    }

    private function assertParametersYamlContent(): void
    {
        $parametersContent = file_get_contents(
            vfsStream::url('root/var/configuration/shops/parameters.yaml')
        );
        $parameters = Yaml::parse($parametersContent);

        $this->assertArrayHasKey('parameters', $parameters);
        $this->assertArrayHasKey('oxid_esales.shop_admin_url', $parameters['parameters']);
        $this->assertEquals(
            'https://admin.example.com',
            $parameters['parameters']['oxid_esales.shop_admin_url']
        );
    }

    private function assertBackupFileExists(): void
    {
        $this->assertFileExists($this->pathProvider->getConfigurationBackupPath());
    }

    private function assertOriginalConfigRemoved(): void
    {
        $this->assertFileDoesNotExist($this->pathProvider->getConfigurationFilePath());
    }

    public function testMigrationWithMissingConfigFile(): void
    {
        $this->filesystem->remove($this->pathProvider->getConfigurationFilePath());

        $this->expectException(ConfigurationFileNotFoundException::class);
        $this->migrator->migrate();
    }

    private function getTestConfig(): string
    {
        return <<<'PHP'
<?php
$this->dbHost = 'localhost';
$this->dbUser = 'user';
$this->dbPwd = 'pass';
$this->dbName = 'oxid';
$this->dbPort = '3306';
$this->sShopURL = 'https://example.com';
$this->sAdminSSLURL = 'https://admin.example.com';
$this->iDebug = 4;
$this->sLogLevel = 'debug';
PHP;
    }
}