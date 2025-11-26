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
use OxidEsales\EshopCommunity\Internal\Transition\Utility\BasicContextInterface;
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

    private Connection $connection;
    private string $productsPicturesDirectory;

    public function __construct(
        ConnectionFactoryInterface $connectionFactory,
    ) {
        $this->connection = $connectionFactory->create();
        $this->productsPicturesDirectory = Path::join('out/pictures/master/product');
    }

    public function migrate(int $batchSize = 5000): Iterator
    {
        $totalProducts = $this->getTotalProductsToMigrate();
        $processedCount = 0;
        $lastProcessedId = null;

        yield ['processed' => $processedCount, 'total' => $totalProducts];

        while (true) {
            $products = $this->fetchProductsBatch($lastProcessedId, $batchSize);

            if (empty($products)) {
                break;
            }

            $this->migrateProductsBatch($products);

            $processedCount += count($products);
            $lastProcessedId = end($products)['OXID'];

            yield ['processed' => $processedCount, 'total' => $totalProducts];
        }
    }

    private function getTotalProductsToMigrate(): int
    {
        $sql = "SELECT COUNT(*) FROM oxarticles a
                LEFT JOIN oxproduct_media pm ON a.OXID = pm.product_id
                WHERE " . self::UNMIGRATED_PRODUCTS_CONDITION;

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

            $mediaRows[] = [$mediaId, $path];
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

            $mediaRows[] = [$mediaId, $path];
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
