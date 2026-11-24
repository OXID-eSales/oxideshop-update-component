<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Integration\SslUrl\Migration;

use Doctrine\DBAL\ArrayParameterType;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\ContextInterface;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use OxidEsales\OxidEshopUpdateComponent\SslUrl\Migration\SslUrlConfigurationMigrator;
use OxidEsales\OxidEshopUpdateComponent\SslUrl\Migration\SslUrlConfigurationMigratorInterface;

final class SslUrlConfigurationMigratorTest extends IntegrationTestCase
{
    private const SHOP_ID = 1;
    private const URL_VARIABLES = ['sMallShopURL', 'sMallSSLShopURL', 'aLanguageURLs', 'aLanguageSSLURLs'];
    private array $originalRows = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->originalRows = $this->get(QueryBuilderFactoryInterface::class)->create()
            ->select('oxid', 'oxshopid', 'oxmodule', 'oxvarname', 'oxvartype', 'oxvarvalue')
            ->from('oxconfig')
            ->where('oxvarname IN (:names)')
            ->setParameter(
                'names',
                self::URL_VARIABLES,
                ArrayParameterType::STRING
            )
            ->executeQuery()
            ->fetchAllAssociative();

        $this->deleteUrlVariables();
    }

    public function tearDown(): void
    {
        $this->deleteUrlVariables();

        foreach ($this->originalRows as $row) {
            $this->get(QueryBuilderFactoryInterface::class)->create()
                ->insert('oxconfig')
                ->values(array_map(
                    static fn (string $column): string => ':' . $column,
                    array_combine(array_keys($row), array_keys($row))
                ))
                ->setParameters($row)
                ->executeStatement();
        }

        parent::tearDown();
    }

    public function testSslMallUrlOverwritesMallUrl(): void
    {
        $this->insertConfigValue('sMallShopURL', 'str', 'http://oxid-eshop.com/');
        $this->insertConfigValue('sMallSSLShopURL', 'str', 'https://oxid-eshop.com/');

        $this->get(SslUrlConfigurationMigratorInterface::class)->migrate();

        $this->assertSame('https://oxid-eshop.com/', $this->fetchConfigValue('sMallShopURL'));
    }

    public function testMallUrlIsCreatedFromSslMallUrl(): void
    {
        $this->insertConfigValue('sMallSSLShopURL', 'str', 'https://oxid-ssl-eshop.com/');
        $this->get(SslUrlConfigurationMigratorInterface::class)->migrate();

        $this->assertSame('https://oxid-ssl-eshop.com/', $this->fetchConfigValue('sMallShopURL'));
    }

    public function testEmptySslMallUrlKeepsMallUrl(): void
    {
        $this->insertConfigValue('sMallShopURL', 'str', 'https://oxid-eshop.com/');
        $this->insertConfigValue('sMallSSLShopURL', 'str', '');
        $this->get(SslUrlConfigurationMigratorInterface::class)->migrate();

        $this->assertSame('https://oxid-eshop.com/', $this->fetchConfigValue('sMallShopURL'));
    }

    public function testSslMallUrlOverwritesEmptyMallUrl(): void
    {
        $this->insertConfigValue('sMallShopURL', 'str', '');
        $this->insertConfigValue('sMallSSLShopURL', 'str', 'https://ssl-shop.com/');

        $this->get(SslUrlConfigurationMigratorInterface::class)->migrate();

        $this->assertSame('https://ssl-shop.com/', $this->fetchConfigValue('sMallShopURL'));
        $this->assertSame(1, $this->countConfigValues('sMallShopURL'));
    }

    public function testMallUrlIsMigratedForAllShops(): void
    {
        $this->insertConfigValue('sMallSSLShopURL', 'str', 'https://oxid-eshop-one.com/', 1);
        $this->insertConfigValue('sMallSSLShopURL', 'str', 'https://oxid-eshop-two.com/', 2);

        $context = $this->createStub(ContextInterface::class);
        $context->method('getAllShopIds')->willReturn([1, 2]);
        $migrator = new SslUrlConfigurationMigrator($context, $this->get(QueryBuilderFactoryInterface::class));

        $migrator->migrate();

        $this->assertSame('https://oxid-eshop-one.com/', $this->fetchConfigValue('sMallShopURL', 1));
        $this->assertSame('https://oxid-eshop-two.com/', $this->fetchConfigValue('sMallShopURL', 2));
    }

    public function testAllShopsAreMigratedWhenMallUrlAlreadyExists(): void
    {
        $this->insertConfigValue('sMallShopURL', 'str', 'http://oxid-eshop-one.com/', 1);
        $this->insertConfigValue('sMallSSLShopURL', 'str', 'https://oxid-eshop-one.com/', 1);
        $this->insertConfigValue('sMallSSLShopURL', 'str', 'https://oxid-eshop-two.com/', 2);

        $context = $this->createStub(ContextInterface::class);
        $context->method('getAllShopIds')->willReturn([1, 2]);
        $migrator = new SslUrlConfigurationMigrator($context, $this->get(QueryBuilderFactoryInterface::class));

        $migrator->migrate();

        $this->assertSame('https://oxid-eshop-one.com/', $this->fetchConfigValue('sMallShopURL', 1));
        $this->assertSame('https://oxid-eshop-two.com/', $this->fetchConfigValue('sMallShopURL', 2));
    }

    public function testSslLanguageUrlsOverwriteLanguageUrlsPerLanguage(): void
    {
        $this->insertConfigValue('aLanguageURLs', 'arr', serialize([
            0 => 'http://oxid-eshop.de/',
            1 => 'http://oxid-eshop.com/'
        ]));
        $this->insertConfigValue('aLanguageSSLURLs', 'arr', serialize([
            0 => 'https://oxid-eshop.de/',
            1 => ''
        ]));

        $this->get(SslUrlConfigurationMigratorInterface::class)->migrate();

        $this->assertSame([
            0 => 'https://oxid-eshop.de/',
            1 => 'http://oxid-eshop.com/'
        ],
            unserialize((string) $this->fetchConfigValue('aLanguageURLs'))
        );
    }

    public function testNullSslLanguageUrlKeepsLanguageUrl(): void
    {
        $this->insertConfigValue('aLanguageURLs', 'arr', serialize([
            0 => 'https://oxid-eshop.de/',
            1 => 'https://oxid-eshop.com/'
        ]));
        $this->insertConfigValue('aLanguageSSLURLs', 'arr', serialize([
            0 => '',
            1 => null
        ]));

        $this->get(SslUrlConfigurationMigratorInterface::class)->migrate();

        $this->assertSame([
            0 => 'https://oxid-eshop.de/',
            1 => 'https://oxid-eshop.com/'
        ],
            unserialize((string) $this->fetchConfigValue('aLanguageURLs'))
        );
    }

    public function testSslLanguageUrlsWithoutValuesDoNotCreateLanguageUrls(): void
    {
        $this->insertConfigValue('aLanguageSSLURLs', 'arr', serialize([
            0 => '',
            1 => null
        ]));

        $this->get(SslUrlConfigurationMigratorInterface::class)->migrate();
        $this->assertFalse($this->fetchConfigValue('aLanguageURLs'));
    }

    public function testSslVariablesAreRemovedForAllShops(): void
    {
        $this->insertConfigValue('sMallSSLShopURL', 'str', 'https://oxid-eshop-one.com/', 1);
        $this->insertConfigValue('sMallSSLShopURL', 'str', '', 2);
        $this->insertConfigValue('aLanguageSSLURLs', 'arr', serialize([0 => 'https://oxid-eshop.de/']), 1);
        $this->insertConfigValue('aLanguageSSLURLs', 'arr', serialize([0 => '', 1 => null]), 2);

        $context = $this->createStub(ContextInterface::class);
        $context->method('getAllShopIds')->willReturn([1, 2]);
        $migrator = new SslUrlConfigurationMigrator($context, $this->get(QueryBuilderFactoryInterface::class));

        $migrator->migrate();

        foreach ([1, 2] as $shopId) {
            $this->assertFalse($this->fetchConfigValue('sMallSSLShopURL', $shopId));
            $this->assertFalse($this->fetchConfigValue('aLanguageSSLURLs', $shopId));
        }
    }

    private function insertConfigValue(string $name, string $type, string $value, int $shopId = self::SHOP_ID): void
    {
        $this->get(QueryBuilderFactoryInterface::class)->create()
            ->insert('oxconfig')
            ->values([
                'oxid' => ':id',
                'oxshopid' => ':shopId',
                'oxmodule' => ':module',
                'oxvarname' => ':name',
                'oxvartype' => ':type',
                'oxvarvalue' => ':value',
            ])
            ->setParameters([
                'id' => bin2hex(random_bytes(16)),
                'shopId' => $shopId,
                'module' => '',
                'name' => $name,
                'type' => $type,
                'value' => $value,
            ])
            ->executeStatement();
    }

    private function fetchConfigValue(string $name, int $shopId = self::SHOP_ID): string|false
    {
        return $this->get(QueryBuilderFactoryInterface::class)->create()
            ->select('oxvarvalue')
            ->from('oxconfig')
            ->where('oxshopid = :shopId')
            ->andWhere('oxmodule = :module')
            ->andWhere('oxvarname = :name')
            ->setParameters([
                'shopId' => $shopId,
                'module' => '',
                'name' => $name
            ])
            ->executeQuery()
            ->fetchOne();
    }

    private function countConfigValues(string $name): int
    {
        return (int) $this->get(QueryBuilderFactoryInterface::class)->create()
            ->select('count(*)')
            ->from('oxconfig')
            ->where('oxshopid = :shopId')
            ->andWhere('oxmodule = :module')
            ->andWhere('oxvarname = :name')
            ->setParameters([
                'shopId' => self::SHOP_ID,
                'module' => '',
                'name' => $name
            ])
            ->executeQuery()
            ->fetchOne();
    }

    private function deleteUrlVariables(): void
    {
        $this->get(QueryBuilderFactoryInterface::class)->create()
            ->delete('oxconfig')
            ->where('oxvarname IN (:names)')
            ->setParameter('names', self::URL_VARIABLES, ArrayParameterType::STRING)
            ->executeStatement();
    }
}
