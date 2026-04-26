<?php

declare(strict_types=1);

namespace Intervention\ImageHash\Tests\Providers;

use Generator;
use Intervention\ImageHash\Hash;
use Intervention\ImageHash\Strategies\Average;
use Intervention\ImageHash\Strategies\Block;
use Intervention\ImageHash\Strategies\Difference;
use Intervention\ImageHash\Strategies\Perceptual;
use stdClass;

class HashDataProvider
{
    public static function provideHashHexInputs(): Generator
    {
        yield [
            '0',
            '00000000',
        ];
        yield [
            '00',
            '00000000',
        ];
        yield [
            'ff',
            '11111111',
        ];
        yield [
            'ffff',
            '1111111111111111',
        ];
        yield [
            'ffffff',
            '111111111111111111111111',
        ];
        yield [
            'ff0055',
            '111111110000000001010101',
        ];
        yield [
            '123456',
            '000100100011010001010110',
        ];
        yield [
            'aaccee',
            '101010101100110011101110',
        ];
        yield [
            'ffffffffffffffffffffffff',
            '111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111',
        ];
        yield [
            'FF00FFFFFF00FF0044FFCC00',
            '111111110000000011111111111111111111111100000000111111110000000001000100111111111100110000000000',
        ];
    }

    public static function provideHashBitInputs(): Generator
    {
        yield [
            '0',
            '00',
        ];
        yield [
            '00',
            '00',
        ];
        yield [
            '0000',
            '00',
        ];
        yield [
            '00000000',
            '00',
        ];
        yield [
            '11111111',
            'ff',
        ];
        yield [
            '1111111111111111',
            'ffff',
        ];
        yield [
            '111111111111111111111111',
            'ffffff',
        ];
        yield [
            '111111110000000001010101',
            'ff0055',
        ];
        yield [
            '000100100011010001010110',
            '123456',
        ];
        yield [
            '101010101100110011101110',
            'aaccee',
        ];
        yield [
            '111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111',
            'ffffffffffffffffffffffff',
        ];
        yield [
            '111111110000000011111111111111111111111100000000111111110000000001000100111111111100110000000000',
            'ff00ffffff00ff0044ffcc00',
        ];

        yield [
            [1, 1, 1, 1, 1, 0, 1, 0],
            'fa',
        ];

        yield [
            ['1', '1', '1', '1', '1', '0', '1', '0'],
            'fa',
        ];

        yield [
            [true, true, true, true, true, false, true, false],
            'fa',
        ];
    }

    public static function provideHashUtf8Inputs(): Generator
    {
        yield ['X', '01011000'];
        yield ['XA', '0101100001000001'];
        yield ['😀', '11110000100111111001100010000000'];
    }

    public static function provideHashDecimalInputs(): Generator
    {
        yield ['0', '00'];
        yield ['1', '01'];
        yield ['255', 'ff'];
        yield ['256', '0100'];
        yield ['16711935', 'ff00ff'];
        yield ['4294967295', 'ffffffff'];
        yield [
            '6277101735386680763835789423207666416102355444464034512895',
            'ffffffffffffffffffffffffffffffffffffffffffffffff',
        ];

        yield [0, '00'];
        yield [1, '01'];
        yield [255, 'ff'];
        yield [256, '0100'];
        yield [16711935, 'ff00ff'];
        yield [4294967295, 'ffffffff'];
        yield [0xFFFF, 'ffff'];
    }

    public static function provideHashDecimalOutputs(): Generator
    {
        yield ['0', '0'];
        yield ['00', '0'];
        yield ['01', '1'];
        yield ['0001', '1'];
        yield ['ff', '255'];
        yield ['0100', '256'];
        yield ['ff00ff', '16711935'];
        yield ['ffffffff', '4294967295'];
        yield [
            'ffffffffffffffffffffffffffffffffffffffffffffffff',
            '6277101735386680763835789423207666416102355444464034512895',
        ];
    }

    public static function provideHashDistances(): Generator
    {
        yield ['aaccee', 'aaccee', 0];
        yield ['FFFFFF01', 'FFFFFF00', 1];
        yield ['FFFFFF02', 'FFFFFF00', 1];
        yield ['FFFFFFaa', 'FFFFFF00', 4];
        yield ['00', 'FF', 8];
    }

    public static function provideHashEquals(): Generator
    {
        yield [Hash::fromHex('0'), Hash::fromHex('00'), true];
        yield [Hash::fromHex('ffffff'), Hash::fromHex('ff0055'), false];
    }

    public static function provideInvalidHexInputs(): Generator
    {
        yield [''];
        yield [' '];
        yield ['x'];
        yield ['ffaaxx'];
        yield ['ffaa '];
    }

    public static function provideInvalidBitInputs(): Generator
    {
        yield [''];
        yield [' '];
        yield ['x'];
        yield ['2'];
        yield ['12'];
        yield [[]];
        yield [[' ']];
        yield [['x']];
        yield [['2']];
        yield [['12']];
        yield [['1', '0', '2']];
        yield [[new stdClass()]];
        yield [[2]];
        yield [[1, 0, 2]];
    }

    public static function provideInvalidUtf8Inputs(): Generator
    {
        yield [''];
    }

    public static function providePrecalculatedHashes(): Generator
    {
        yield [
            new Average(),
            __DIR__ . '/../images/mountain_day.jpg',
            'ffffff0700000000',
        ];

        yield [
            new Block(),
            __DIR__ . '/../images/mountain_day.jpg',
            '00006082ffbeff9fff1fff7f003c00001fff1edf041f0006c4fcfc009f00cbfc',
        ];

        yield [
            new Difference(),
            __DIR__ . '/../images/mountain_day.jpg',
            '6c2b58432011e38e',
        ];

        yield [
            new Perceptual(),
            __DIR__ . '/../images/mountain_day.jpg',
            '84e4d9011332ae60',
        ];
    }
}
