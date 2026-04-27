<?php

declare(strict_types=1);

namespace Intervention\ImageHash\Tests\Unit;

use Intervention\Image\Exceptions\InvalidArgumentException;
use Intervention\ImageHash\Hash;
use Intervention\ImageHash\Interfaces\HashInterface;
use Intervention\ImageHash\Tests\Providers\HashDataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;

class HashTest extends TestCase
{
    #[DataProviderExternal(HashDataProvider::class, 'provideHashHexInputs')]
    public function testFromHex(string $hex, string $bits): void
    {
        $this->assertEquals($bits, Hash::fromHex($hex)->toBits());
    }

    #[DataProviderExternal(HashDataProvider::class, 'provideHashBitInputs')]
    public function testFromBits(string|array $bits, string $hex): void
    {
        $this->assertEquals($hex, Hash::fromBits($bits)->toHex());
    }

    #[DataProviderExternal(HashDataProvider::class, 'provideHashByteInputs')]
    public function testFromBytes(string $bytes, string $bits): void
    {
        $this->assertEquals($bits, Hash::fromBytes($bytes)->toBits());
    }

    #[DataProviderExternal(HashDataProvider::class, 'provideHashDistances')]
    public function testDistance(string $a, string $b, int $distance): void
    {
        $this->assertEquals($distance, Hash::fromHex($a)->distance(Hash::fromHex($b)));
    }

    #[DataProviderExternal(HashDataProvider::class, 'provideHashEquals')]
    public function testEquals(HashInterface $a, HashInterface $b, bool $equal): void
    {
        $this->assertEquals($equal, $a->equals($b));
    }

    #[DataProviderExternal(HashDataProvider::class, 'provideInvalidHexInputs')]
    public function testInvalidHexInputs(mixed $input): void
    {
        $this->expectException(InvalidArgumentException::class);
        Hash::fromHex($input);
    }

    #[DataProviderExternal(HashDataProvider::class, 'provideInvalidBitInputs')]
    public function testInvalidBitInputs(mixed $input): void
    {
        $this->expectException(InvalidArgumentException::class);
        Hash::fromBits($input);
    }

    #[DataProviderExternal(HashDataProvider::class, 'provideInvalidByteInputs')]
    public function testInvalidByteInputs(mixed $input): void
    {
        $this->expectException(InvalidArgumentException::class);
        Hash::fromBytes($input);
    }

    #[DataProviderExternal(HashDataProvider::class, 'provideHashHexInputs')]
    public function testHexRoundTrips(mixed ...$input): void
    {
        $hex = strtolower($input[0]);
        $this->assertEquals($hex, Hash::fromHex($hex)->toHex());
    }

    #[DataProviderExternal(HashDataProvider::class, 'provideHashByteInputs')]
    public function testByteRoundTrips(mixed ...$input): void
    {
        $this->assertEquals($input[0], Hash::fromBytes($input[0])->toBytes());
    }

    #[DataProviderExternal(HashDataProvider::class, 'provideHashBitInputs')]
    public function testBitRoundTrips(mixed ...$input): void
    {
        $bits = $input[0];
        if (is_string($bits)) {
            $this->assertEquals($bits, Hash::fromBytes($bits)->toBytes());
        }
    }
}
