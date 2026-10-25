<?php

declare(strict_types=1);

namespace Intervention\ImageHash\Tests\Unit;

use Generator;
use Intervention\Image\Exceptions\InvalidArgumentException;
use Intervention\ImageHash\BitParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

class BitParserTest extends TestCase
{
    #[DataProvider('provideValidInputOutput')]
    public function testParseValid(mixed $inputBit, string $outputBit): void
    {
        $this->assertEquals($outputBit, (string) new BitParser($inputBit));
    }

    public static function provideValidInputOutput(): Generator
    {
        yield [0, '0'];
        yield [1, '1'];
        yield ['0', '0'];
        yield ['1', '1'];
        yield [false, '0'];
        yield [true, '1'];
    }

    #[DataProvider('provideInvalidInput')]
    public function testParseInvalid(mixed $input): void
    {
        $this->expectException(InvalidArgumentException::class);
        (string) new BitParser($input);
    }

    public static function provideInvalidInput(): Generator
    {
        yield [2];
        yield [22];
        yield [''];
        yield ['2'];
        yield ['22'];
        yield ['test'];
        yield [' '];
        yield [null];
        yield [new stdClass()];
    }
}
