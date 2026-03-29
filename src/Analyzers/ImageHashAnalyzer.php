<?php

declare(strict_types=1);

namespace Intervention\ImageHash\Analyzers;

use Intervention\Image\Interfaces\AnalyzerInterface;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\ImageHash\Interfaces\HashInterface;
use Intervention\ImageHash\Interfaces\StrategyInterface;
use Intervention\ImageHash\Strategies\Difference;

class ImageHashAnalyzer implements AnalyzerInterface
{
    public function __construct(protected StrategyInterface $strategy = new Difference())
    {
        //
    }

    public function analyze(ImageInterface $image): HashInterface
    {
        return $this->strategy->hash(clone $image);
    }
}
