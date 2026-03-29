<?php

declare(strict_types=1);

namespace Intervention\ImageHash\Strategies;

use Intervention\Image\Exceptions\InvalidArgumentException;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\ImageHash\Hash;
use Intervention\ImageHash\Interfaces\StrategyInterface;
use Intervention\ImageHash\Analyzers\RgbArrayAnalyzer;

class Perceptual implements StrategyInterface
{
    public const string AVERAGE = 'average';
    public const string MEDIAN = 'median';

    public function __construct(protected int $size = 32, protected string $comparisonMethod = self::AVERAGE)
    {
        if (!in_array($this->comparisonMethod, [self::AVERAGE, self::MEDIAN])) {
            throw new InvalidArgumentException('Unknown comparison mode ' . $comparisonMethod);
        }
    }

    public function hash(ImageInterface $image): Hash
    {
        $resized = $image->resize($this->size, $this->size);

        $matrix = [];
        $row = [];
        $rows = [];
        $col = [];

        for ($y = 0; $y < $this->size; $y++) {
            for ($x = 0; $x < $this->size; $x++) {
                $rgb = $resized->analyze(new RgbArrayAnalyzer($x, $y));
                $row[$x] = (int) floor(($rgb[0] * 0.299) + ($rgb[1] * 0.587) + ($rgb[2] * 0.114));
            }
            $rows[$y] = $this->calculateDCT($row);
        }

        for ($x = 0; $x < $this->size; $x++) {
            for ($y = 0; $y < $this->size; $y++) {
                $col[$y] = $rows[$y][$x];
            }
            $matrix[$x] = $this->calculateDCT($col);
        }

        // Extract the top 8x8 pixels.
        $pixels = [];
        for ($y = 0; $y < 8; $y++) {
            for ($x = 0; $x < 8; $x++) {
                $pixels[] = $matrix[$y][$x];
            }
        }

        if ($this->comparisonMethod === self::MEDIAN) {
            $compare = $this->median($pixels);
        } else {
            $compare = $this->average($pixels);
        }

        // Calculate hash.
        $bits = [];
        foreach ($pixels as $pixel) {
            $bits[] = (int) ($pixel > $compare);
        }

        return Hash::fromBits($bits);
    }

    /**
     * Perform a 1 dimension Discrete Cosine Transformation.
     *
     * @param array<int|float> $matrix
     * @return array<float>
     */
    protected function calculateDCT(array $matrix): array
    {
        $transformed = [];
        $size = count($matrix);

        for ($i = 0; $i < $size; $i++) {
            $sum = 0;
            for ($j = 0; $j < $size; $j++) {
                $sum += $matrix[$j] * cos($i * pi() * ($j + 0.5) / $size);
            }
            $sum *= sqrt(2 / $size);
            if ($i === 0) {
                $sum *= 1 / sqrt(2);
            }
            $transformed[$i] = $sum;
        }

        return $transformed;
    }

    /**
     * Get the median of the pixel values.
     *
     * @param array<float> $pixels
     */
    protected function median(array $pixels): float
    {
        sort($pixels, SORT_NUMERIC);

        if (count($pixels) % 2 === 0) {
            return ($pixels[count($pixels) / 2 - 1] + $pixels[count($pixels) / 2]) / 2;
        }

        return $pixels[(int) floor(count($pixels) / 2)];
    }

    /**
     * Get the average of the pixel values.
     *
     * @param array<float> $pixels
     */
    protected function average(array $pixels): float
    {
        // Calculate the average value from top 8x8 pixels, except for the first one.
        $n = count($pixels) - 1;

        return array_sum(array_slice($pixels, 1, $n)) / $n;
    }
}
