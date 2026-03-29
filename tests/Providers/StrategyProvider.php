<?php

declare(strict_types=1);

namespace Intervention\ImageHash\Tests\Providers;

use Generator;
use Intervention\ImageHash\Strategies\Average;
use Intervention\ImageHash\Strategies\Block;
use Intervention\ImageHash\Strategies\Difference;
use Intervention\ImageHash\Strategies\Perceptual;

class StrategyProvider
{
    public static function providerStrategies(): Generator
    {
        yield [new Average()];
        yield [new Difference()];
        yield [new Perceptual(32, Perceptual::AVERAGE)];
        yield [new Perceptual(32, Perceptual::MEDIAN)];
        yield [new Block(8, Block::QUICK)];
        yield [new Block(8, Block::PRECISE)];
    }
}
