<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\ProductImage\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Iterator;
use OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Database\Id;
use Symfony\Component\Filesystem\Path;
use Throwable;

class ProductImageMigrator implements ProductImageMigratorInterface
{
    private const UNMIGRATED_PRODUCTS_CONDITION = "
        pm.product_id IS NULL
        AND (
            a.OXICON != '' OR a.OXTHUMB != '' OR
            a.OXPIC1 != '' OR a.OXPIC2 != '' OR a.OXPIC3 != '' OR a.OXPIC4 != '' OR
            a.OXPIC5 != '' OR a.OXPIC6 != '' OR a.OXPIC7 != '' OR a.OXPIC8 != '' OR
            a.OXPIC9 != '' OR a.OXPIC10 != '' OR a.OXPIC11 != '' OR a.OXPIC12 != ''
        )";

    private const UNMIGRATED_VARIANTS_WITHOUT_IMAGES_CONDITION = "
        v.OXPARENTID != ''
        AND vpm.product_id IS NULL
        AND (v.OXICON = '' OR v.OXICON IS NULL)
        AND (v.OXTHUMB = '' OR v.OXTHUMB IS NULL)
        AND (v.OXPIC1 = '' OR v.OXPIC1 IS NULL)
        AND (v.OXPIC2 = '' OR v.OXPIC2 IS NULL)
        AND (v.OXPIC3 = '' OR v.OXPIC3 IS NULL)
        AND (v.OXPIC4 = '' OR v.OXPIC4 IS NULL)
        AND (v.OXPIC5 = '' OR v.OXPIC5 IS NULL)
        AND (v.OXPIC6 = '' OR v.OXPIC6 IS NULL)
        AND (v.OXPIC7 = '' OR v.OXPIC7 IS NULL)
        AND (v.OXPIC8 = '' OR v.OXPIC8 IS NULL)
        AND (v.OXPIC9 = '' OR v.OXPIC9 IS NULL)
        AND (v.OXPIC10 = '' OR v.OXPIC10 IS NULL)
        AND (v.OXPIC11 = '' OR v.OXPIC11 IS NULL)
        AND (v.OXPIC12 = '' OR v.OXPIC12 IS NULL)";

    private Connection $connection;
    private string $productsPicturesDirectory;

    public function __construct(
        ConnectionFactoryInterface $connectionFactory,
    ) {
        $this->connection = $connectionFactory->create();
        $this->productsPicturesDirectory = Path::join('out/pictures/master/product');
    }

    public function migrateProducts(int $batchSize = 5000): Iterator
    {
        $total = $this->getTotalProductsToMigrate();
        $processedCount = 0;
        $lastProcessedId = null;

        yield ['processed' => $processedCount, 'total' => $total];

        while (true) {
            $products = $this->fetchProductsBatch($lastProcessedId, $batchSize);

            if (empty($products)) {
                break;
            }

            $this->migrateProductsBatch($products);

            $processedCount += count($products);
            $lastProcessedId = end($products)['OXID'];

            yield ['processed' => $processedCount, 'total' => $total];
        }
    }

    public function migrateVariants(int $batchSize = 5000): Iterator
    {
        $total = $this->getTotalVariantsToMigrate();
        $processedCount = 0;
        $lastProcessedId = null;

        yield ['processed' => $processedCount, 'total' => $total];

        while (true) {
            $variantIds = $this->fetchVariantIdsBatch($lastProcessedId, $batchSize);

            if (empty($variantIds)) {
                break;
            }

            $this->migrateVariantsBatch($variantIds);

            $processedCount += count($variantIds);
            $lastProcessedId = end($variantIds);

            yield ['processed' => $processedCount, 'total' => $total];
        }
    }

    private function getTotalProductsToMigrate(): int
    {
        $sql = "SELECT COUNT(*) FROM oxarticles a
                LEFT JOIN oxproduct_media pm ON a.OXID = pm.product_id
                WHERE " . self::UNMIGRATED_PRODUCTS_CONDITION;

        return (int) $this->connection->executeQuery($sql)->fetchOne();
    }

    private function getTotalVariantsToMigrate(): int
    {
        $sql = "SELECT COUNT(DISTINCT v.OXID)
                FROM oxarticles v
                JOIN oxarticles p ON v.OXPARENTID = p.OXID
                JOIN oxproduct_media ppm ON p.OXID = ppm.product_id
                LEFT JOIN oxproduct_media vpm ON v.OXID = vpm.product_id
                WHERE " . self::UNMIGRATED_VARIANTS_WITHOUT_IMAGES_CONDITION;

        return (int) $this->connection->executeQuery($sql)->fetchOne();
    }

    private function fetchProductsBatch(?string $lastProcessedId, int $batchSize): array
    {
        $sql = "SELECT a.OXID, a.OXICON, a.OXTHUMB, a.OXPIC1, a.OXPIC2, a.OXPIC3, a.OXPIC4, a.OXPIC5, a.OXPIC6,
                       a.OXPIC7, a.OXPIC8, a.OXPIC9, a.OXPIC10, a.OXPIC11, a.OXPIC12
                FROM oxarticles a
                LEFT JOIN oxproduct_media pm ON a.OXID = pm.product_id
                WHERE " . self::UNMIGRATED_PRODUCTS_CONDITION;

        $params = [];
        $types = [];
        if ($lastProcessedId !== null) {
            $sql .= ' AND a.OXID > :lastProcessedId';
            $params['lastProcessedId'] = $lastProcessedId;
            $types['lastProcessedId'] = ParameterType::STRING;
        }

        $sql .= ' ORDER BY a.OXID LIMIT :batchSize';
        $params['batchSize'] = $batchSize;
        $types['batchSize'] = ParameterType::INTEGER;

        return $this->connection->executeQuery($sql, $params, $types)->fetchAllAssociative();
    }

    private function fetchVariantIdsBatch(?string $lastProcessedId, int $batchSize): array
    {
        $sql = "SELECT DISTINCT v.OXID
                FROM oxarticles v
                JOIN oxarticles p ON v.OXPARENTID = p.OXID
                JOIN oxproduct_media ppm ON p.OXID = ppm.product_id
                LEFT JOIN oxproduct_media vpm ON v.OXID = vpm.product_id
                WHERE " . self::UNMIGRATED_VARIANTS_WITHOUT_IMAGES_CONDITION;

        $params = [];
        $types = [];

        if ($lastProcessedId !== null) {
            $sql .= ' AND v.OXID > :lastProcessedId';
            $params['lastProcessedId'] = $lastProcessedId;
            $types['lastProcessedId'] = ParameterType::STRING;
        }

        $sql .= ' ORDER BY v.OXID LIMIT :batchSize';
        $params['batchSize'] = $batchSize;
        $types['batchSize'] = ParameterType::INTEGER;

        return $this->connection->executeQuery($sql, $params, $types)->fetchFirstColumn();
    }

    private function migrateProductsBatch(array $products): void
    {
        $mediaRows = [];
        $productMediaRows = [];
        $productMediaRolesRows = [];

        foreach ($products as $product) {
            $this->collectImageDataForProduct($product, $mediaRows, $productMediaRows, $productMediaRolesRows);
        }

        if (empty($mediaRows)) {
            return;
        }

        $this->connection->beginTransaction();
        try {
            $this->insertMedia($mediaRows);
            $this->insertProductMedia($productMediaRows);
            $this->insertProductMediaRoles($productMediaRolesRows);
            $this->connection->commit();
        } catch (Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    private function migrateVariantsBatch(array $variantIds): void
    {
        if (empty($variantIds)) {
            return;
        }

        $this->connection->beginTransaction();
        try {
            $this->insertVariantProductMedia($variantIds);
            $this->insertVariantProductMediaRoles($variantIds);
            $this->connection->commit();
        } catch (Throwable $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    private function collectImageDataForProduct(
        array $product,
        array &$mediaRows,
        array &$productMediaRows,
        array &$productMediaRolesRows
    ): void {
        $productId = $product['OXID'];
        $position = 1;

        for ($i = 1; $i <= 12; $i++) {
            $picField = 'OXPIC' . $i;

            if (!empty($product[$picField])) {
                $mediaId = $this->generateUuid();
                $productMediaId = $this->generateUuid();
                $fileName = $product[$picField];
                $path = Path::join($this->productsPicturesDirectory, "$i/$fileName");

                $mediaRows[] = [$mediaId, $path, $this->getMimeType($path)];
                $productMediaRows[] = [
                    $productMediaId,
                    $productId,
                    $mediaId,
                    $position,
                ];
                $productMediaRolesRows[] = [$productMediaId, 'detail'];

                $position++;
            }
        }

        if (!empty($product['OXICON'])) {
            $mediaId = $this->generateUuid();
            $productMediaId = $this->generateUuid();
            $fileName = $product['OXICON'];
            $path = Path::join($this->productsPicturesDirectory, "icon/$fileName");

            $mediaRows[] = [$mediaId, $path, $this->getMimeType($path)];
            $productMediaRows[] = [
                $productMediaId,
                $productId,
                $mediaId,
                0,
            ];
            $productMediaRolesRows[] = [$productMediaId, 'icon'];
        }

        if (!empty($product['OXTHUMB'])) {
            $mediaId = $this->generateUuid();
            $productMediaId = $this->generateUuid();
            $fileName = $product['OXTHUMB'];
            $path = Path::join($this->productsPicturesDirectory, "thumb/$fileName");

            $mediaRows[] = [$mediaId, $path, $this->getMimeType($path)];
            $productMediaRows[] = [
                $productMediaId,
                $productId,
                $mediaId,
                0,
            ];
            $productMediaRolesRows[] = [$productMediaId, 'thumbnail'];
        }
    }

    private function insertMedia(array $mediaRows): void
    {
        $values = [];
        $params = [];

        foreach ($mediaRows as $row) {
            $values[] = '(?, ?, ?, NOW())';
            array_push($params, ...$row);
        }

        $sql = 'INSERT INTO oxmedia (id, path, type, created) VALUES ' . implode(', ', $values);
        $this->connection->executeStatement($sql, $params);
    }

    private function insertProductMedia(array $productMediaRows): void
    {
        $values = [];
        $params = [];

        foreach ($productMediaRows as $row) {
            $values[] = '(?, ?, ?, ?, 1, NOW())';
            array_push($params, ...$row);
        }

        $sql = 'INSERT INTO oxproduct_media (id, product_id, media_id, position, active, created) VALUES '
            . implode(', ', $values);
        $this->connection->executeStatement($sql, $params);
    }

    private function insertProductMediaRoles(array $productMediaRolesRows): void
    {
        $values = [];
        $params = [];

        foreach ($productMediaRolesRows as $row) {
            $values[] = '(?, ?, NOW())';
            array_push($params, ...$row);
        }

        $sql = 'INSERT INTO oxproduct_media_roles (product_media_id, role, created) VALUES '
            . implode(', ', $values);
        $this->connection->executeStatement($sql, $params);
    }

    private function insertVariantProductMedia(array $variantIds): void
    {
        $placeholders = implode(',', array_fill(0, count($variantIds), '?'));

        $sql = "INSERT INTO oxproduct_media (id, product_id, media_id, position, active, created)
                SELECT
                    REPLACE(UUID(), '-', ''),
                    v.OXID,
                    pm.media_id,
                    pm.position,
                    pm.active,
                    NOW()
                FROM oxarticles v
                JOIN oxproduct_media pm ON v.OXPARENTID = pm.product_id
                WHERE v.OXID IN ($placeholders)";

        $this->connection->executeStatement($sql, $variantIds);
    }

    private function insertVariantProductMediaRoles(array $variantIds): void
    {
        $placeholders = implode(',', array_fill(0, count($variantIds), '?'));

        $sql = "INSERT INTO oxproduct_media_roles (product_media_id, role, created)
                SELECT vpm.id, pmr.role, NOW()
                FROM oxproduct_media vpm
                JOIN oxarticles v ON vpm.product_id = v.OXID
                JOIN oxproduct_media pm ON v.OXPARENTID = pm.product_id
                    AND vpm.media_id = pm.media_id
                JOIN oxproduct_media_roles pmr ON pm.id = pmr.product_media_id
                WHERE v.OXID IN ($placeholders)
                AND NOT EXISTS (
                    SELECT 1 FROM oxproduct_media_roles existing
                    WHERE existing.product_media_id = vpm.id
                )";

        $this->connection->executeStatement($sql, $variantIds);
    }

    private function generateUuid(): string
    {
        return (string)Id::generate();
    }

    private function getMimeType(string $relativePath): string
    {
        $extension = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));

        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'bmp' => 'image/bmp',
            'svg' => 'image/svg+xml',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'ico' => 'image/x-icon',
        ];

        return $mimeTypes[$extension] ?? '';
    }
}
