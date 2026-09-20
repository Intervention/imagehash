<?php

declare(strict_types=1);

namespace Intervention\ImageHash\Tests\Providers;

use Generator;
use Intervention\ImageHash\Strategies;

class StrategyProvider
{
    public static function providerStrategies(): Generator
    {
        yield [new Strategies\AverageStrategy()];
        yield [new Strategies\DifferenceStrategy()];
        yield [new Strategies\PerceptualStrategy(32, Strategies\PerceptualStrategy::AVERAGE)];
        yield [new Strategies\PerceptualStrategy(32, Strategies\PerceptualStrategy::MEDIAN)];
        yield [new Strategies\BlockStrategy(8, Strategies\BlockStrategy::QUICK)];
        yield [new Strategies\BlockStrategy(8, Strategies\BlockStrategy::PRECISE)];
    }
}
