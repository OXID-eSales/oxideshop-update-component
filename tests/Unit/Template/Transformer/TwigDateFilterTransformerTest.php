<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Unit\Template\Transformer;

use OxidEsales\OxidEshopUpdateComponent\Template\Exception\FileNotWritableException;
use OxidEsales\OxidEshopUpdateComponent\Template\FileReader\FileReader;
use OxidEsales\OxidEshopUpdateComponent\Template\Transformer\TwigDateFilterTransformer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

final class TwigDateFilterTransformerTest extends TestCase
{
    private TwigDateFilterTransformer $twigFilterTransformer;
    private Filesystem $filesystemMock;
    private FileReader $fileReaderMock;

    protected function setUp(): void
    {
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->fileReaderMock = $this->createMock(FileReader::class);
        $this->twigFilterTransformer = new TwigDateFilterTransformer($this->filesystemMock, $this->fileReaderMock);
    }

    public function testReplaceThrowsExceptionWhenFileNotWritable(): void
    {
        $this->filesystemMock
            ->method('exists')
            ->with('template.twig')
            ->willReturn(true);

        $this->fileReaderMock
            ->method('readFile')
            ->with('template.twig')
            ->willReturn('|date_format("%d/%m/%Y")');

        $this->filesystemMock
            ->method('dumpFile')
            ->willThrowException($this->createMock(IOExceptionInterface::class));

        $this->expectException(FileNotWritableException::class);
        $this->twigFilterTransformer->replace('template.twig');
    }

    public static function dateFormatProvider(): array
    {
        return [
            'standard case' => [
                'templateContent' => 'Hello {{ today | date_format("%d/%m/%Y") }}',
                'expectedContent' => 'Hello {{ today | date(\'d/m/Y\') }}',
            ],
            'extra spaces before filter' => [
                'templateContent' => 'Hello {{ today |    date_format("%d/%m/%Y") }}',
                'expectedContent' => 'Hello {{ today | date(\'d/m/Y\') }}',
            ],
            'different format' => [
                'templateContent' => 'Hello {{ today | date_format("%Y-%m-%d") }}',
                'expectedContent' => 'Hello {{ today | date(\'Y-m-d\') }}',
            ],
            'nested case' => [
                'templateContent' => 'Date: {{ item.date | date_format("%d-%m-%Y") | upper }}',
                'expectedContent' => 'Date: {{ item.date | date(\'d-m-Y\') | upper }}',
            ],
            'time with two digits' => [
                'templateContent' => 'Current time: {{ now | date_format("%H:%M:%S") }}',
                'expectedContent' => 'Current time: {{ now | date(\'H:i:s\') }}',
            ],
            'day name full' => [
                'templateContent' => 'Day: {{ today | date_format("%A, %d %B %Y") }}',
                'expectedContent' => 'Day: {{ today | date(\'l, d F Y\') }}',
            ],
            'day name abbreviated' => [
                'templateContent' => 'Day: {{ today | date_format("%a, %d %b %Y") }}',
                'expectedContent' => 'Day: {{ today | date(\'D, d M Y\') }}',
            ],
            'mixed time and date' => [
                'templateContent' => 'It is {{ now | date_format("%A, %d %B %Y, %H:%M:%S") }}',
                'expectedContent' => 'It is {{ now | date(\'l, d F Y, H:i:s\') }}',
            ]
        ];
    }

    #[DataProvider('dateFormatProvider')]
    public function testReplaceSuccessfullyReplacesDateFormatFilter(
        string $templateContent,
        string $expectedContent
    ): void {
        $this->filesystemMock
            ->method('exists')
            ->with('template.twig')
            ->willReturn(true);

        $this->fileReaderMock
            ->method('readFile')
            ->with('template.twig')
            ->willReturn($templateContent);

        $this->filesystemMock
            ->expects($this->once())
            ->method('dumpFile')
            ->with('template.twig', $expectedContent);

        $this->twigFilterTransformer->replace('template.twig');
    }
}
