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
     * Convert hash into a concatinated string of bits.
     */
    public function toBits(): string;

    /**
     * Return bytes of hash.
     */
    public function toBytes(): string;

    /**
     * Return base64-encoded bytes of hash.
     */
    public function toBase64(): string;

    /**
     * Return bit length of hash.
     */
    public function bitLength(): int;
}
