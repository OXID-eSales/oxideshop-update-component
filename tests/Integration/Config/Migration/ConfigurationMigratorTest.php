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

final class ConfigurationMigratorTest extends TestCase
{
    private ConfigurationMigrator $migrator;
    private BasicContextInterface $contextMock;

    protected function setUp(): void
    {
        $this->setUpVirtualFileSystem();
        $this->initializeMocks();
        $this->initializeMigrator();
    }

    private function setUpVirtualFileSystem(): void
    {
        vfsStream::setup('root', null, [
            'source' => [
                'config.inc.php' => $this->getTestConfig(),
            ],
            'shop' => [],
            'var' => [
                'configuration' => [
                    'shops' => [],
                ],
            ],
        ]);
    }

    private function initializeMocks(): void
    {
        $this->contextMock = $this->createMock(BasicContextInterface::class);
        $this->contextMock->method('getSourcePath')
            ->willReturn(vfsStream::url('root/source'));
        $this->contextMock->method('getShopRootPath')
            ->willReturn(vfsStream::url('root/shop'));
        $this->contextMock->method('getProjectConfigurationDirectory')
            ->willReturn(vfsStream::url('root/var/configuration/shops'));
    }

    private function initializeMigrator(): void
    {
        $provider = new ConfigurationPathProvider($this->contextMock);
        $validator = new ConfigurationValidator($provider);
        $reader = new ConfigurationReader($provider);
        $transformer = new ConfigurationTransformer();
        $writer = new ConfigurationWriter($this->contextMock, new Filesystem());
        $this->migrator = new ConfigurationMigrator(
            $validator,
            $reader,
            $transformer,
            $writer
        );
    }

    public function testSuccessfulConfigurationMigration(): void
    {
        $this->migrator->migrate();
        $this->assertEnvFileContent();
        $this->assertParametersYamlContent();
    }

    public function testNonExistentConfigurationFile(): void
    {
        $provider = new ConfigurationPathProvider($this->contextMock);
        (new Filesystem())->remove($provider->getConfigurationFilePath());
        $this->expectException(ConfigurationFileNotFoundException::class);
        $this->migrator->migrate();
    }

    public function testMigrationWithExistingParametersFile(): void
    {
        $this->createExistingParametersFile();

        $this->migrator->migrate();

        $this->assertExistingAndNewParameters();
    }

    private function createExistingParametersFile(): void
    {
        $existingParameters = [
            'parameters' => [
                'oxid_esales.existing_parameter' => 'existing_value',
                'oxid_esales.shop_admin_url' => 'https://old-admin.example.com'
            ]
        ];

        $parametersPath = vfsStream::url('root/var/configuration/shops/parameters.yaml');
        (new Filesystem())->dumpFile(
            $parametersPath,
            Yaml::dump($existingParameters)
        );
    }

    private function assertExistingAndNewParameters(): void
    {
        $parametersPath = vfsStream::url('root/var/configuration/shops/parameters.yaml');
        $result = Yaml::parseFile($parametersPath);

        $this->assertArrayHasKey('parameters', $result);
        $this->assertArrayHasKey('oxid_esales.existing_parameter', $result['parameters']);
        $this->assertEquals('existing_value', $result['parameters']['oxid_esales.existing_parameter']);
        $this->assertArrayHasKey('oxid_esales.shop_admin_url', $result['parameters']);
        $this->assertEquals('https://admin.example.com', $result['parameters']['oxid_esales.shop_admin_url']);
    }

    private function assertEnvFileContent(): void
    {
        $envContent = file_get_contents(vfsStream::url('root/shop/.env'));
        $this->assertStringContainsString('OXID_DEBUG_MODE=true', $envContent);
        $this->assertStringContainsString('OXID_LOG_LEVEL=debug', $envContent);
        $this->assertStringContainsString('OXID_SHOP_BASE_URL=https://example.com', $envContent);
        $this->assertStringContainsString('OXID_DB_URL=mysql://user:pass@localhost', $envContent);
    }

    private function assertParametersYamlContent(): void
    {
        $parametersContent = file_get_contents(vfsStream::url('root/var/configuration/shops/parameters.yaml'));
        $parameters = Yaml::parse($parametersContent);
        $this->assertArrayHasKey('parameters', $parameters);
        $this->assertArrayHasKey('oxid_esales.shop_admin_url', $parameters['parameters']);
        $this->assertEquals('https://admin.example.com', $parameters['parameters']['oxid_esales.shop_admin_url']);
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
