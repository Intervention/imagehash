<?php

declare(strict_types=1);

namespace Intervention\ImageHash\Strategies;

use Intervention\Image\Interfaces\ImageInterface;
use Intervention\ImageHash\Hash;
use Intervention\ImageHash\Interfaces\StrategyInterface;
use Intervention\ImageHash\Analyzers\RgbArrayAnalyzer;

class Average implements StrategyInterface
{
    public function __construct(protected int $size = 8)
    {
        //
    }

    /**
     * {@inheritdoc}
     *
     * @see StrategyInterface::hash()
     */
    public function hash(ImageInterface $image): Hash
    {
        $resized = $image->resize($this->size, $this->size);

        // Create an array of greyscale pixel values.
        $pixels = [];
        for ($y = 0; $y < $this->size; $y++) {
            for ($x = 0; $x < $this->size; $x++) {
                $rgb = $resized->analyze(new RgbArrayAnalyzer($x, $y));
                $pixels[] = (int) floor(($rgb[0] * 0.299) + ($rgb[1] * 0.587) + ($rgb[2] * 0.114));
            }
        }

        // Get the average pixel value.
        $average = floor(array_sum($pixels) / count($pixels));

        // Each hash bit is set based on whether the current pixels value is above or below the average.
        $bits = array_map(fn(int $pixel): int => (int) ($pixel > $average), $pixels);

        return Hash::fromBits($bits);
    }
}
