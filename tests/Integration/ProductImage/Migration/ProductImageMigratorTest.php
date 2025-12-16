<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Integration\ProductImage\Migration;

use OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Tests\ContainerTrait;
use OxidEsales\EshopCommunity\Tests\DatabaseTrait;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use OxidEsales\OxidEshopUpdateComponent\ProductImage\Migration\ProductImageMigrator;
use OxidEsales\OxidEshopUpdateComponent\ProductImage\Migration\ProductImageMigratorInterface;

final class ProductImageMigratorTest extends IntegrationTestCase
{
    use DatabaseTrait;
    use ContainerTrait;

    private ProductImageMigratorInterface $migrator;

    public function setUp(): void
    {
        parent::setUp();

        $connection = $this->get(ConnectionFactoryInterface::class)->create();
        $this->beginTransaction($connection);
        $this->prepareDatabase($connection);

        $this->migrator = new ProductImageMigrator(
            $this->get(ConnectionFactoryInterface::class)
        );
    }

    public function tearDown(): void
    {
        $connection = $this->get(ConnectionFactoryInterface::class)->create();
        $this->rollBackTransaction($connection);

        parent::tearDown();
    }

    public function testMigrateProductWithAllImages(): void
    {
        $productId = $this->createProduct([
            'OXPIC1' => 'product1_pic1.jpg',
            'OXPIC2' => 'product1_pic2.jpg',
            'OXICON' => 'product1_icon.jpg',
            'OXTHUMB' => 'product1_thumb.jpg',
        ]);

        $this->runMigration();

        $this->assertProductMediaCount($productId, 4);
        $this->assertMediaExists('out/pictures/master/product/1/product1_pic1.jpg');
        $this->assertMediaExists('out/pictures/master/product/2/product1_pic2.jpg');
        $this->assertMediaExists('out/pictures/master/product/icon/product1_icon.jpg');
        $this->assertMediaExists('out/pictures/master/product/thumb/product1_thumb.jpg');
        $this->assertProductHasRole($productId, 'detail', 2);
        $this->assertProductHasRole($productId, 'icon', 1);
        $this->assertProductHasRole($productId, 'thumbnail', 1);
    }

    public function testMigrateProductWithOnlyDetailImages(): void
    {
        $productId = $this->createProduct([
            'OXPIC1' => 'product_pic1.jpg',
            'OXPIC3' => 'product_pic3.jpg',
        ]);

        $this->runMigration();

        $this->assertProductMediaCount($productId, 2);
        $this->assertMediaExists('out/pictures/master/product/1/product_pic1.jpg');
        $this->assertMediaExists('out/pictures/master/product/3/product_pic3.jpg');
        $this->assertProductHasRole($productId, 'detail', 2);
    }

    public function testMigrateProductWithNoImages(): void
    {
        $productId = $this->createProduct([]);

        $this->runMigration();

        $this->assertProductMediaCount($productId, 0);
    }

    public function testMigrateMultipleProducts(): void
    {
        $productId1 = $this->createProduct([
            'OXPIC1' => 'p1_pic1.jpg',
            'OXICON' => 'p1_icon.jpg',
        ]);
        $productId2 = $this->createProduct([
            'OXPIC1' => 'p2_pic1.jpg',
            'OXTHUMB' => 'p2_thumb.jpg',
        ]);

        $this->runMigration();

        $this->assertProductMediaCount($productId1, 2);
        $this->assertProductMediaCount($productId2, 2);
    }

    public function testMigrateSkipsAlreadyMigratedProducts(): void
    {
        $productId = $this->createProduct([
            'OXPIC1' => 'product_pic1.jpg',
        ]);

        $this->runMigration();
        $initialCount = $this->getMediaCount();

        $this->runMigration();

        $this->assertEquals($initialCount, $this->getMediaCount());
        $this->assertProductMediaCount($productId, 1);
    }

    public function testMigrateProductsReportsProgress(): void
    {
        $this->createProduct(['OXPIC1' => 'p1.jpg']);
        $this->createProduct(['OXPIC1' => 'p2.jpg']);

        $progressReports = [];
        foreach ($this->migrator->migrateProducts(10) as $progress) {
            $progressReports[] = $progress;
        }

        $this->assertNotEmpty($progressReports);
        $this->assertEquals(2, $progressReports[0]['total']);
        $this->assertEquals(0, $progressReports[0]['processed']);
        $this->assertEquals(2, end($progressReports)['processed']);
    }

    public function testMigrateProductsWithCustomBatchSize(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->createProduct(['OXPIC1' => "product{$i}.jpg"]);
        }

        $batchCount = 0;
        foreach ($this->migrator->migrateProducts(2) as $progress) {
            $batchCount++;
        }

        $this->assertGreaterThan(2, $batchCount);
    }

    public function testMigrateCreatesCorrectPositions(): void
    {
        $productId = $this->createProduct([
            'OXPIC1' => 'pic1.jpg',
            'OXPIC2' => 'pic2.jpg',
            'OXPIC3' => 'pic3.jpg',
        ]);

        $this->runMigration();

        $positions = $this->getProductMediaPositions($productId);
        $this->assertEquals([1, 2, 3], $positions);
    }

    public function testMigrateCreatesZeroPositionForIconAndThumbnail(): void
    {
        $productId = $this->createProduct([
            'OXICON' => 'icon.jpg',
            'OXTHUMB' => 'thumb.jpg',
        ]);

        $this->runMigration();

        $positions = $this->getProductMediaPositions($productId);
        $this->assertEquals([0, 0], $positions);
    }

    public function testMigrateCreatesCorrectMimeTypes(): void
    {
        $this->createProduct([
            'OXPIC1' => 'product.jpg',
            'OXPIC2' => 'product.png',
            'OXICON' => 'icon.gif',
            'OXTHUMB' => 'thumb.webp',
        ]);

        $this->runMigration();

        $this->assertMediaHasType('out/pictures/master/product/1/product.jpg', 'image/jpeg');
        $this->assertMediaHasType('out/pictures/master/product/2/product.png', 'image/png');
        $this->assertMediaHasType('out/pictures/master/product/icon/icon.gif', 'image/gif');
        $this->assertMediaHasType('out/pictures/master/product/thumb/thumb.webp', 'image/webp');
    }

    private function runMigration(): void
    {
        foreach ($this->migrator->migrateProducts() as $progress) {
        }
        foreach ($this->migrator->migrateVariants() as $progress) {
        }
    }

    private function createProduct(array $images): string
    {
        $productId = bin2hex(random_bytes(16));

        $queryBuilder = $this->get(QueryBuilderFactoryInterface::class)->create();
        $queryBuilder
            ->insert('oxarticles')
            ->values([
                'OXID' => ':id',
                'OXTITLE' => ':title',
                'OXACTIVE' => ':active',
                'OXPIC1' => ':oxpic1',
                'OXPIC2' => ':oxpic2',
                'OXPIC3' => ':oxpic3',
                'OXPIC4' => ':oxpic4',
                'OXPIC5' => ':oxpic5',
                'OXPIC6' => ':oxpic6',
                'OXPIC7' => ':oxpic7',
                'OXPIC8' => ':oxpic8',
                'OXPIC9' => ':oxpic9',
                'OXPIC10' => ':oxpic10',
                'OXPIC11' => ':oxpic11',
                'OXPIC12' => ':oxpic12',
                'OXICON' => ':oxicon',
                'OXTHUMB' => ':oxthumb',
            ])
            ->setParameters([
                'id' => $productId,
                'title' => 'Test Product',
                'active' => 1,
                'oxpic1' => $images['OXPIC1'] ?? '',
                'oxpic2' => $images['OXPIC2'] ?? '',
                'oxpic3' => $images['OXPIC3'] ?? '',
                'oxpic4' => $images['OXPIC4'] ?? '',
                'oxpic5' => $images['OXPIC5'] ?? '',
                'oxpic6' => $images['OXPIC6'] ?? '',
                'oxpic7' => $images['OXPIC7'] ?? '',
                'oxpic8' => $images['OXPIC8'] ?? '',
                'oxpic9' => $images['OXPIC9'] ?? '',
                'oxpic10' => $images['OXPIC10'] ?? '',
                'oxpic11' => $images['OXPIC11'] ?? '',
                'oxpic12' => $images['OXPIC12'] ?? '',
                'oxicon' => $images['OXICON'] ?? '',
                'oxthumb' => $images['OXTHUMB'] ?? '',
            ])
            ->executeQuery();

        return $productId;
    }

    private function assertProductMediaCount(string $productId, int $expectedCount): void
    {
        $connection = $this->get(ConnectionFactoryInterface::class)->create();
        $count = (int) $connection->executeQuery(
            'SELECT COUNT(*) FROM oxproduct_media WHERE product_id = ?',
            [$productId]
        )->fetchOne();

        $this->assertEquals($expectedCount, $count);
    }

    private function assertMediaExists(string $path): void
    {
        $connection = $this->get(ConnectionFactoryInterface::class)->create();
        $count = (int) $connection->executeQuery(
            'SELECT COUNT(*) FROM oxmedia WHERE path = ?',
            [$path]
        )->fetchOne();

        $this->assertEquals(1, $count, "Media with path '$path' should exist");
    }

    private function assertMediaHasType(string $path, string $expectedType): void
    {
        $connection = $this->get(ConnectionFactoryInterface::class)->create();
        $type = $connection->executeQuery(
            'SELECT type FROM oxmedia WHERE path = ?',
            [$path]
        )->fetchOne();

        $this->assertEquals($expectedType, $type, "Media with path '$path' should have type '$expectedType'");
    }

    private function assertProductHasRole(string $productId, string $role, int $expectedCount): void
    {
        $connection = $this->get(ConnectionFactoryInterface::class)->create();
        $count = (int) $connection->executeQuery(
            'SELECT COUNT(*) FROM oxproduct_media_roles pmr
             JOIN oxproduct_media pm ON pmr.product_media_id = pm.id
             WHERE pm.product_id = ? AND pmr.role = ?',
            [$productId, $role]
        )->fetchOne();

        $this->assertEquals($expectedCount, $count, "Product should have $expectedCount media with role '$role'");
    }

    private function getMediaCount(): int
    {
        $connection = $this->get(ConnectionFactoryInterface::class)->create();
        return (int) $connection->executeQuery('SELECT COUNT(*) FROM oxmedia')->fetchOne();
    }

    private function getProductMediaPositions(string $productId): array
    {
        $connection = $this->get(ConnectionFactoryInterface::class)->create();
        $result = $connection->executeQuery(
            'SELECT position FROM oxproduct_media WHERE product_id = ? ORDER BY position',
            [$productId]
        )->fetchAllAssociative();

        return array_map(static fn($row) => (int) $row['position'], $result);
    }

    public function testMigrateVariantWithoutImagesInheritsFromParent(): void
    {
        $parentId = $this->createProduct([
            'OXPIC1' => 'parent_pic1.jpg',
            'OXPIC2' => 'parent_pic2.jpg',
            'OXICON' => 'parent_icon.jpg',
        ]);
        $variantId = $this->createVariant($parentId, []);

        $this->runMigration();

        $this->assertProductMediaCount($parentId, 3);
        $this->assertProductMediaCount($variantId, 3);
        $this->assertVariantSharesParentMedia($parentId, $variantId);
    }

    public function testMigrateVariantWithOwnImagesDoesNotInherit(): void
    {
        $parentId = $this->createProduct([
            'OXPIC1' => 'parent_pic1.jpg',
        ]);
        $variantId = $this->createVariant($parentId, [
            'OXPIC1' => 'variant_pic1.jpg',
        ]);

        $this->runMigration();

        $this->assertProductMediaCount($parentId, 1);
        $this->assertProductMediaCount($variantId, 1);
        // Variant should have its own media (not shared)
        $this->assertVariantHasOwnMedia($variantId);
    }

    public function testMigrateVariantPreservesParentRoles(): void
    {
        $parentId = $this->createProduct([
            'OXPIC1' => 'parent_pic1.jpg',
            'OXICON' => 'parent_icon.jpg',
            'OXTHUMB' => 'parent_thumb.jpg',
        ]);
        $variantId = $this->createVariant($parentId, []);

        $this->runMigration();

        $this->assertProductHasRole($variantId, 'detail', 1);
        $this->assertProductHasRole($variantId, 'icon', 1);
        $this->assertProductHasRole($variantId, 'thumbnail', 1);
    }

    public function testMigrateVariantPreservesParentPositions(): void
    {
        $parentId = $this->createProduct([
            'OXPIC1' => 'pic1.jpg',
            'OXPIC2' => 'pic2.jpg',
            'OXPIC3' => 'pic3.jpg',
        ]);
        $variantId = $this->createVariant($parentId, []);

        $this->runMigration();

        $parentPositions = $this->getProductMediaPositions($parentId);
        $variantPositions = $this->getProductMediaPositions($variantId);
        $this->assertEquals($parentPositions, $variantPositions);
    }

    public function testMigrateMultipleVariantsWithoutImages(): void
    {
        $parentId = $this->createProduct([
            'OXPIC1' => 'parent_pic.jpg',
        ]);
        $variant1Id = $this->createVariant($parentId, []);
        $variant2Id = $this->createVariant($parentId, []);

        $this->runMigration();

        $this->assertProductMediaCount($parentId, 1);
        $this->assertProductMediaCount($variant1Id, 1);
        $this->assertProductMediaCount($variant2Id, 1);
    }

    public function testMigrateVariantsReportsProgress(): void
    {
        $parentId = $this->createProduct(['OXPIC1' => 'parent.jpg']);
        $this->createVariant($parentId, []);

        // First migrate products
        foreach ($this->migrator->migrateProducts(10) as $progress) {
        }

        // Then check variant migration progress
        $progressReports = [];
        foreach ($this->migrator->migrateVariants(10) as $progress) {
            $progressReports[] = $progress;
        }

        $this->assertNotEmpty($progressReports);
        $this->assertEquals(1, $progressReports[0]['total']);
        $this->assertEquals(0, $progressReports[0]['processed']);
        $this->assertEquals(1, end($progressReports)['processed']);
    }

    private function createVariant(string $parentId, array $images): string
    {
        $variantId = bin2hex(random_bytes(16));

        $queryBuilder = $this->get(QueryBuilderFactoryInterface::class)->create();
        $queryBuilder
            ->insert('oxarticles')
            ->values([
                'OXID' => ':id',
                'OXPARENTID' => ':parentId',
                'OXTITLE' => ':title',
                'OXACTIVE' => ':active',
                'OXPIC1' => ':oxpic1',
                'OXPIC2' => ':oxpic2',
                'OXPIC3' => ':oxpic3',
                'OXPIC4' => ':oxpic4',
                'OXPIC5' => ':oxpic5',
                'OXPIC6' => ':oxpic6',
                'OXPIC7' => ':oxpic7',
                'OXPIC8' => ':oxpic8',
                'OXPIC9' => ':oxpic9',
                'OXPIC10' => ':oxpic10',
                'OXPIC11' => ':oxpic11',
                'OXPIC12' => ':oxpic12',
                'OXICON' => ':oxicon',
                'OXTHUMB' => ':oxthumb',
            ])
            ->setParameters([
                'id' => $variantId,
                'parentId' => $parentId,
                'title' => 'Test Variant',
                'active' => 1,
                'oxpic1' => $images['OXPIC1'] ?? '',
                'oxpic2' => $images['OXPIC2'] ?? '',
                'oxpic3' => $images['OXPIC3'] ?? '',
                'oxpic4' => $images['OXPIC4'] ?? '',
                'oxpic5' => $images['OXPIC5'] ?? '',
                'oxpic6' => $images['OXPIC6'] ?? '',
                'oxpic7' => $images['OXPIC7'] ?? '',
                'oxpic8' => $images['OXPIC8'] ?? '',
                'oxpic9' => $images['OXPIC9'] ?? '',
                'oxpic10' => $images['OXPIC10'] ?? '',
                'oxpic11' => $images['OXPIC11'] ?? '',
                'oxpic12' => $images['OXPIC12'] ?? '',
                'oxicon' => $images['OXICON'] ?? '',
                'oxthumb' => $images['OXTHUMB'] ?? '',
            ])
            ->executeQuery();

        return $variantId;
    }

    private function assertVariantSharesParentMedia(string $parentId, string $variantId): void
    {
        $connection = $this->get(ConnectionFactoryInterface::class)->create();

        $parentMediaIds = $connection->executeQuery(
            'SELECT media_id FROM oxproduct_media WHERE product_id = ? ORDER BY media_id',
            [$parentId]
        )->fetchFirstColumn();

        $variantMediaIds = $connection->executeQuery(
            'SELECT media_id FROM oxproduct_media WHERE product_id = ? ORDER BY media_id',
            [$variantId]
        )->fetchFirstColumn();

        $this->assertEquals($parentMediaIds, $variantMediaIds, 'Variant should share same media_ids as parent');
    }

    private function assertVariantHasOwnMedia(string $variantId): void
    {
        $connection = $this->get(ConnectionFactoryInterface::class)->create();

        // Get the variant's parent
        $parentId = $connection->executeQuery(
            'SELECT OXPARENTID FROM oxarticles WHERE OXID = ?',
            [$variantId]
        )->fetchOne();

        $parentMediaIds = $connection->executeQuery(
            'SELECT media_id FROM oxproduct_media WHERE product_id = ?',
            [$parentId]
        )->fetchFirstColumn();

        $variantMediaIds = $connection->executeQuery(
            'SELECT media_id FROM oxproduct_media WHERE product_id = ?',
            [$variantId]
        )->fetchFirstColumn();

        $this->assertNotEquals($parentMediaIds, $variantMediaIds, 'Variant should have its own media_ids');
    }

    private function prepareDatabase($connection): void
    {
        $connection->executeStatement('DELETE FROM oxproduct_media_roles');
        $connection->executeStatement('DELETE FROM oxproduct_media');
        $connection->executeStatement('DELETE FROM oxmedia');
        $connection->executeStatement('DELETE FROM oxarticles');
    }
}
