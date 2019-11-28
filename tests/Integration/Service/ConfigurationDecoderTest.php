<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Integration\Service;

use Doctrine\DBAL\Query\QueryBuilder;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\ContextInterface;
use OxidEsales\EshopCommunity\Tests\Integration\Internal\ContainerTrait;
use OxidEsales\TestingLibrary\Services\Library\DatabaseRestorer\DatabaseRestorer;
use PHPUnit\Framework\TestCase;

final class ConfigurationDecoderTest extends TestCase
{
    use ContainerTrait;

    /**
     * @var DatabaseRestorer
     */
    private $databaseRestorer;

    public function setUp(): void
    {
        parent::setUp();
        $this->databaseRestorer = new DatabaseRestorer();
        $this->databaseRestorer->dumpDB(__CLASS__);
    }

    public function tearDown(): void
    {
        $this->databaseRestorer->restoreDB(__CLASS__);
        parent::tearDown();
    }

    public function testSuccessfulDecoding(): void
    {
        $value = 'test_value_to_check';
        $id = '_testId';

        $this->insertEncodedEntryToDatabase($id, $value);

        $this->get('oxid_esales.oxid_eshop_update_component.configuration_decoder')->decode();

        $valueFromDatabase = $this->fetchValueFromDatabase($id);

        $this->assertSame($value, $valueFromDatabase);
    }

    private function createQueryBuilder(): QueryBuilder
    {
        return $this->get(QueryBuilderFactoryInterface::class)->create();
    }

    /**
     * @param string $id
     * @param string $value
     */
    private function insertEncodedEntryToDatabase(string $id, string $value): void
    {
        $queryBuilder = $this->createQueryBuilder();
        $queryBuilder
            ->insert('oxconfig')
            ->values([
                'oxid' => ':id',
                'oxvarvalue' => 'encode(:value, :key)',
            ])
            ->setParameters([
                'id' => $id,
                'value' => $value,
                'key' => $this->get(ContextInterface::class)->getConfigurationEncryptionKey(),
            ]);

        $queryBuilder->execute();
    }

    private function fetchValueFromDatabase(string $id): string
    {
        $queryBuilder = $this->createQueryBuilder();
        $queryBuilder
            ->select(['oxid', 'oxvarvalue'])
            ->from('oxconfig')
            ->where('oxid = :oxid')
            ->setParameters([
                'oxid' => $id,
            ]);

        $result = $queryBuilder->execute()->fetch();

        return $result['oxvarvalue'];
    }
}
