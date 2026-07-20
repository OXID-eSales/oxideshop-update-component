<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\ThemeMetadata\Writer;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

readonly class YamlFileWriter implements YamlFileWriterInterface
{
    public function write(string $filePath, array $data): void
    {
        (new Filesystem())->dumpFile($filePath, Yaml::dump($data, 10, 2));
    }
}
