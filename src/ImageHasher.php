<?php

declare(strict_types=1);

namespace Intervention\ImageHash;

use Intervention\Image\Exceptions\InvalidArgumentException;
use Intervention\ImageHash\Analyzers\ImageHashAnalyzer;
use Intervention\ImageHash\Interfaces\HashInterface;
use Intervention\ImageHash\Interfaces\ImageHasherInterface;
use Intervention\ImageHash\Interfaces\StrategyInterface;
use Intervention\ImageHash\Strategies\Difference;
use Intervention\Image\Interfaces\DriverInterface;
use Intervention\Image\Traits\CanResolveDriver;

class ImageHasher implements ImageHasherInterface
{
    use CanResolveDriver;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        public string|DriverInterface $driver,
        public StrategyInterface $strategy = new Difference(),
    ) {
        $this->driver = $this->resolveDriver($driver);
    }

    /**
     * Create image hasher statically.
     *
     * @throws InvalidArgumentException
     */
    public static function create(string|DriverInterface $driver, StrategyInterface $strategy = new Difference()): self
    {
        return new self($driver, $strategy);
    }

    /**
     * Create image hasher with given image manipulation driver.
     *
     * @throws InvalidArgumentException
     */
    public static function usingDriver(string|DriverInterface $driver): self
    {
        return new self($driver);
    }

    /**
     * Create a hasher instance with the given driver from the current one.
     *
     * @throws InvalidArgumentException
     */
    public function withDriver(string|DriverInterface $driver): self
    {
        return new self($driver, $this->strategy);
    }

    /**
     * Create a hasher instance with the given strategy from the current one.
     *
     * @throws InvalidArgumentException
     */
    public function withStrategy(StrategyInterface $strategy): self
    {
        return new self($this->driver, $strategy);
    }

    /**
     * {@inheritdoc}
     *
     * @see ImageHasherInterface::hash()
     */
    public function hash(mixed $image): HashInterface
    {
        return $this->driver->decodeImage($image)->analyze(new ImageHashAnalyzer($this->strategy));
    }
}
