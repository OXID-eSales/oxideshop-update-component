<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\OxidEshopUpdateComponent\Tests\Unit\ThemeSettings\Transformer;

use OxidEsales\OxidEshopUpdateComponent\ThemeSettings\Transformer\ConfigValueDecoder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ConfigValueDecoderTest extends TestCase
{
    #[DataProvider('provideEncodedValues')]
    public function testDecode(string $type, string $encodedValue, mixed $expected): void
    {
        $this->assertSame($expected, (new ConfigValueDecoder())->decode($type, $encodedValue));
    }

    public static function provideEncodedValues(): array
    {
        return [
            'bool one' => ['bool', '1', true],
            'bool true' => ['bool', 'true', true],
            'bool empty' => ['bool', '', false],
            'bool zero' => ['bool', '0', false],
            'collection' => ['arr', serialize(['10', '20']), ['10', '20']],
            'associative collection' => ['aarr', serialize(['a' => '1']), ['a' => '1']],
            'string' => ['str', 'logo.png', 'logo.png'],
            'select' => ['select', 'grid', 'grid'],
            'number' => ['num', '2.5', '2.5'],
        ];
    }
}
