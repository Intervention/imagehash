<?php

declare(strict_types=1);

namespace Intervention\ImageHash\Tests\Providers;

use Generator;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;

class DriverProvider extends StrategyProvider
{
    public static function provideDrivers(): Generator
    {
        yield [new GdDriver()];
        yield [new ImagickDriver()];
    }

    public static function provideDriversWithStrategies(): Generator
    {
        foreach (self::provideDrivers() as $driver) {
            foreach (self::providerStrategies() as $strategy) {
                yield array_merge($driver, $strategy);
            }
        }
    }
}
