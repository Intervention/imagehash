<?php

declare(strict_types=1);

namespace Intervention\ImageHash\Strategies;

use Intervention\Image\Interfaces\ImageInterface;
use Intervention\ImageHash\Hash;
use Intervention\ImageHash\Interfaces\StrategyInterface;
use Intervention\ImageHash\Analyzers\RgbArrayAnalyzer;
use Intervention\ImageHash\Interfaces\HashInterface;

class Difference implements StrategyInterface
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
    public function hash(ImageInterface $image): HashInterface
    {
        // For this implementation we create a 8x9 image.
        $width = $this->size + 1;
        $height = $this->size;

        $resized = $image->resize($width, $height);

        $bits = [];
        for ($y = 0; $y < $height; $y++) {
            // Get the pixel value for the leftmost pixel.
            $rgb = $resized->analyze(new RgbArrayAnalyzer(0, $y));
            $left = (int) floor(($rgb[0] * 0.299) + ($rgb[1] * 0.587) + ($rgb[2] * 0.114));

            for ($x = 1; $x < $width; $x++) {
                // Get the pixel value for each pixel starting from position 1.
                $rgb = $resized->analyze(new RgbArrayAnalyzer($x, $y));
                $right = (int) floor(($rgb[0] * 0.299) + ($rgb[1] * 0.587) + ($rgb[2] * 0.114));

                // Each hash bit is set based on whether the left pixel is brighter than the right pixel.
                // http://www.hackerfactor.com/blog/index.php?/archives/529-Kind-of-Like-That.html
                $bits[] = (int) ($left > $right);

                // Prepare the next loop.
                $left = $right;
            }
        }

        return Hash::fromBits($bits);
    }
}
