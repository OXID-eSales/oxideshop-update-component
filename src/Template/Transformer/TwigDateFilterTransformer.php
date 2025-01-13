<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Template\Transformer;

use OxidEsales\OxidEshopUpdateComponent\Template\Exception\FileNotWritableException;
use OxidEsales\OxidEshopUpdateComponent\Template\FileReader\FileReader;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

class TwigDateFilterTransformer implements TwigFilterTransformerInterface
{
    private array $conversionMap = [
        '%a' => 'D',
        '%A' => 'l',
        '%d' => 'd',
        '%e' => 'j',
        '%j' => 'z',
        '%u' => 'N',
        '%w' => 'w',
        '%U' => 'W',
        '%V' => 'W',
        '%W' => 'W',
        '%b' => 'M',
        '%B' => 'F',
        '%h' => 'M',
        '%m' => 'm',
        '%C' => 'o',
        '%G' => 'o',
        '%g' => 'y',
        '%y' => 'y',
        '%Y' => 'Y',
        '%H' => 'H',
        '%k' => 'G',
        '%I' => 'h',
        '%l' => 'g',
        '%M' => 'i',
        '%p' => 'A',
        '%P' => 'a',
        '%r' => 'h:i:s A',
        '%R' => 'H:i',
        '%S' => 's',
        '%T' => 'H:i:s',
        '%z' => 'O',
        '%Z' => 'T',
        '%c' => 'r',
        '%D' => 'm/d/y',
        '%F' => 'Y-m-d',
        '%x' => 'd.m.Y',
        '%s' => 'U',
        '%n' => '',
        '%t' => '',
        '%%' => '%',
    ];

    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly FileReader $fileReader
    ) {
    }

    public function replace(string $templateFile): void
    {
        $fileContent = $this->fileReader->readFile($templateFile);

        try {
            $this->filesystem->dumpFile($templateFile, $this->replaceDateFormatFilter($fileContent));
        } catch (IOExceptionInterface) {
            throw new FileNotWritableException();
        }
    }

    private function replaceDateFormatFilter(string $templateFileContent): string
    {
        $pattern = '/\|\s*date_format\(["\'](.*?)["\']\)/';
        return preg_replace_callback($pattern, function ($matches) {
            $strFTimeFormat = $matches[1];
            $dateFormat = strtr($strFTimeFormat, $this->conversionMap);
            return '| date(\'' . $dateFormat . '\')';
        }, $templateFileContent);
    }
}
