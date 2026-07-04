<?php

declare(strict_types=1);

namespace Intervention\ImageHash\Tests\Unit;

use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\ImageHash\ImageHasher;
use Intervention\ImageHash\Interfaces\StrategyInterface;
use Intervention\ImageHash\Tests\Providers\HashDataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;

class PreCalculatedHashTest extends TestCase
{
    #[DataProviderExternal(HashDataProvider::class, 'providePrecalculatedHashes')]
    public function testPrecalculatedHashes(StrategyInterface $strategy, string $path, string $precalculated): void
    {
        foreach ([new GdDriver(), new ImagickDriver()] as $driver) {
            $hex = ImageHasher::create($driver, $strategy)->hash($path)->toHex();
            $this->assertEquals(
                $precalculated,
                $hex,
                "Strategy " . $strategy::class . " with driver " . $driver::class . " generated " .
                    "a different hash for image " . $path . ": " . $hex . ' instead of ' . $precalculated,
            );
        }
    }
}
