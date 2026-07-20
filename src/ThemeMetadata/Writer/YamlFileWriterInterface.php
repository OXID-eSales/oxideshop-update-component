<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\OxidEshopUpdateComponent\ThemeMetadata\Writer;

interface YamlFileWriterInterface
{
    public function write(string $filePath, array $data): void;
}
