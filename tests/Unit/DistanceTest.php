<?php

declare(strict_types=1);

namespace Intervention\ImageHash\Tests\Unit;

use Intervention\Image\Interfaces\DriverInterface;
use Intervention\ImageHash\ImageHasher;
use Intervention\ImageHash\Interfaces\StrategyInterface;
use Intervention\ImageHash\Tests\Providers\DriverProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;

class DistanceTest extends TestCase
{
    #[DataProviderExternal(DriverProvider::class, 'provideDriversWithStrategies')]
    public function testDistanceLow(DriverInterface $driver, StrategyInterface $strategy): void
    {
        $this->assertDistance(
            0,
            3,
            $this->calculateDistance(
                __DIR__ . '/../images/tropical_high.jpg',
                __DIR__ . '/../images/tropical_low.jpg',
                $driver,
                $strategy,
            ),
        );
    }

    #[DataProviderExternal(DriverProvider::class, 'provideDriversWithStrategies')]
    public function testDistanceMedium(DriverInterface $driver, StrategyInterface $strategy): void
    {
        $this->assertDistance(
            3,
            14,
            $this->calculateDistance(
                __DIR__ . '/../images/tropical_high.jpg',
                __DIR__ . '/../images/tropical_watermark.jpg',
                $driver,
                $strategy,
            ),
        );
    }

    #[DataProviderExternal(DriverProvider::class, 'provideDriversWithStrategies')]
    public function testDistanceHigh(DriverInterface $driver, StrategyInterface $strategy): void
    {
        $this->assertDistance(
            8,
            20,
            $this->calculateDistance(
                __DIR__ . '/../images/mountain_day.jpg',
                __DIR__ . '/../images/mountain_night.jpg',
                $driver,
                $strategy,
            ),
        );

        $this->assertDistance(
            11,
            24,
            $this->calculateDistance(
                __DIR__ . '/../images/tropical_high.jpg',
                __DIR__ . '/../images/tropical_crop.jpg',
                $driver,
                $strategy,
            ),
        );
    }

    #[DataProviderExternal(DriverProvider::class, 'provideDriversWithStrategies')]
    public function testDistanceDifferent(DriverInterface $driver, StrategyInterface $strategy): void
    {
        $this->assertGreaterThanOrEqual(
            21,
            $this->calculateDistance(
                __DIR__ . '/../images/tropical_high.jpg',
                __DIR__ . '/../images/mountain_night.jpg',
                $driver,
                $strategy,
            ),
        );
    }

    #[DataProviderExternal(DriverProvider::class, 'provideDriversWithStrategies')]
    public function testDistanceSame(DriverInterface $driver, StrategyInterface $strategy): void
    {
        $this->assertEquals(
            0,
            $this->calculateDistance(
                __DIR__ . '/../images/tropical_high.jpg',
                __DIR__ . '/../images/tropical_high.jpg',
                $driver,
                $strategy,
            ),
        );
    }

    private function calculateDistance(
        string $pathx,
        string $pathy,
        DriverInterface $driver,
        StrategyInterface $strategy,
    ): int {
        $hasher = ImageHasher::create($driver, $strategy);
        $hashx = $hasher->hash($pathx);
        $hashy = $hasher->hash($pathy);

        return $hashx->distance($hashy);
    }

    private function assertDistance(int $min, int $max, int $distance): void
    {
        $this->assertLessThanOrEqual($max, $distance);
        $this->assertGreaterThanOrEqual($min, $distance);
    }
}
