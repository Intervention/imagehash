<?php

declare(strict_types=1);

namespace Intervention\ImageHash\Interfaces;

use Stringable;

interface HashInterface extends Stringable
{
    /**
     * Calculate distance to given hash.
     */
    public function distance(self $hash): int;

    /**
     * Calculate distance to given hash normalized to a value between 0-1.
     */
    public function distanceNormalized(self $hash): float;

    /**
     * Determine if given hash is equal to hash.
     */
    public function equals(self $hash, int $leeway = 0): bool;

    /**
     * Transform hash to hexadecimal string.
     */
    public function toHex(): string;

    /**
     * Transform hash to a base64-encoded string.
     */
    public function toBase64(): string;

    /**
     * Calculate bit length of hash.
     */
    public function bitLength(): int;
}
