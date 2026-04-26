<?php

declare(strict_types=1);

namespace Intervention\ImageHash;

use Intervention\ImageHash\Analyzers\ImageHashAnalyzer;
use Intervention\ImageHash\Interfaces\HashInterface;
use Intervention\ImageHash\Interfaces\ImageHasherInterface;
use Intervention\ImageHash\Interfaces\StrategyInterface;
use Intervention\ImageHash\Strategies\Difference;
use Intervention\Image\Interfaces\DriverInterface;

class ImageHasher implements ImageHasherInterface
{
    public function __construct(
        public DriverInterface $driver,
        public StrategyInterface $strategy = new Difference(),
    ) {
        //
    }

    public static function create(DriverInterface $driver, StrategyInterface $strategy = new Difference()): self
    {
        return new self($driver, $strategy);
    }

    public static function usingDriver(DriverInterface $driver): self
    {
        return new self($driver);
    }

    public function withDriver(DriverInterface $driver): self
    {
        return new self($driver, $this->strategy);
    }

    public function withStrategy(StrategyInterface $strategy): self
    {
        return new self($this->driver, $strategy);
    }

    public function hash(mixed $image): HashInterface
    {
        return $this->driver->decodeImage($image)->analyze(new ImageHashAnalyzer($this->strategy));
    }
}
