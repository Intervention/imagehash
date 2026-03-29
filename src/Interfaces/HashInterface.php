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
     * Determine if given hash is equal to hash.
     */
    public function equals(self $hash): bool;

    /**
     * Transform hash to hexadecimal string.
     */
    public function toHex(): string;

    /**
     * Convert hash into a string of concatinated bits.
     */
    public function toBits(): string;

    /**
     * Transform hash to utf-8 string.
     */
    public function toUtf8(): string;
}
