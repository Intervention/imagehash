<?php

declare(strict_types=1);

namespace Intervention\ImageHash\Tests\Unit;

use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Interfaces\DriverInterface;
use Intervention\ImageHash\ImageHasher;
use Intervention\ImageHash\Interfaces\StrategyInterface;
use Intervention\ImageHash\Strategies\Block;
use Intervention\ImageHash\Strategies\Difference;
use Intervention\ImageHash\Tests\Providers\DriverProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;

class ImageHasherTest extends TestCase
{
    #[DataProviderExternal(DriverProvider::class, 'provideDriversWithStrategies')]
    public function testConstructor(DriverInterface $driver, StrategyInterface $strategy): void
    {
        $this->assertInstanceOf(ImageHasher::class, new ImageHasher($driver, $strategy));
        $this->assertInstanceOf(ImageHasher::class, ImageHasher::create($driver, $strategy));
    }

    #[DataProviderExternal(DriverProvider::class, 'provideDrivers')]
    public function testUsingDriver(DriverInterface $driver): void
    {
        $this->assertInstanceOf(ImageHasher::class, ImageHasher::usingDriver($driver));
    }

    public function testWithDriver(): void
    {
        $gdHasher = new ImageHasher(new GdDriver());
        $this->assertInstanceOf(GdDriver::class, $gdHasher->driver);
        $imagickHasher = $gdHasher->withDriver(new ImagickDriver());
        $this->assertInstanceOf(GdDriver::class, $gdHasher->driver);
        $this->assertInstanceOf(ImagickDriver::class, $imagickHasher->driver);
    }

    public function testWithStrategy(): void
    {
        $differenceHasher = new ImageHasher(new GdDriver(), new Difference());
        $blockHasher = $differenceHasher->withStrategy(new Block());
        $this->assertInstanceOf(Difference::class, $differenceHasher->strategy);
        $this->assertInstanceOf(Block::class, $blockHasher->strategy);
    }
}
