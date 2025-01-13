<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Integration\Template\FileReader;

use OxidEsales\OxidEshopUpdateComponent\Template\Exception\TemplateFileNotFoundException;
use OxidEsales\OxidEshopUpdateComponent\Template\FileReader\FileReader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

final class FileReaderTest extends TestCase
{
    private Filesystem $filesystem;
    private FileReader $fileReader;
    private string $testFilePath;

    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();
        $this->fileReader = new FileReader($this->filesystem);
        $this->testFilePath = sys_get_temp_dir() . '/test_file.txt';
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove($this->testFilePath);
    }

    public function testReadFileWhenFileExists(): void
    {
        $fileContents = 'Hello, World!';
        $this->filesystem->dumpFile($this->testFilePath, $fileContents);

        $result = $this->fileReader->readFile($this->testFilePath);

        $this->assertSame($fileContents, $result);
    }

    public function testReadFileWhenFileDoesNotExist(): void
    {
        $nonExistentFilePath = sys_get_temp_dir() . '/nonexistent_file.txt';

        $this->expectException(TemplateFileNotFoundException::class);
        $this->expectExceptionMessage("File not found: $nonExistentFilePath");

        $this->fileReader->readFile($nonExistentFilePath);
    }
}
