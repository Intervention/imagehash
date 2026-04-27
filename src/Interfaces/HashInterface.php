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
    public function equals(self $hash, int $leeway = 0): bool;

    /**
     * Transform hash to hexadecimal string.
     */
    public function toHex(): string;

    /**
     * Convert hash into a array of bits.
     */
    public function toBits(): array;

    /**
     * Return bytes of hash.
     */
    public function toBytes(): string;

    /**
     * Calculate decimal representation of hash.
     */
    public function toDecimal(): string;

    /**
     * Return bit length of hash.
     */
    public function bitLength(): int;
}
