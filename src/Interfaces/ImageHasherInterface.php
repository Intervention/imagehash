<?php

declare(strict_types=1);

namespace Intervention\ImageHash\Interfaces;

interface ImageHasherInterface
{
    /**
     * Build image hash from given image source which can be one of the following:
     *
     * - Path in filesystem
     * - Raw binary image data
     * - SplFileInfo object
     * - Base64 encoded image data
     * - Data URI string or instance of DataUriInterface
     * - Stream resource
     * - Instance of ImageInterface
     * - Instance of EncodedImageInterface
     */
    public function hash(mixed $image): HashInterface;
}
