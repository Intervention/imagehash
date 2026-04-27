<?php

declare(strict_types=1);

namespace Intervention\ImageHash;

use Intervention\Image\Exceptions\InvalidArgumentException;
use Intervention\Image\Exceptions\RuntimeException;
use Intervention\ImageHash\Interfaces\HashInterface;
use JsonSerializable;
use Stringable;

class Hash implements HashInterface, Stringable, JsonSerializable
{
    /**
     * @throws InvalidArgumentException
     */
    private function __construct(readonly protected string $bytes)
    {
        if (strlen($this->bytes) < 1) {
            throw new InvalidArgumentException("A hash must be created of at least one byte");
        }
    }

    /**
     * Create hash from hexadecimal string.
     */
    public static function fromHex(string $hash): self
    {
        if (strlen($hash) < 2) {
            throw new InvalidArgumentException("Hash must be created of at least one byte hexadecimal string");
        }

        $hash = strtolower($hash);

        if (!ctype_xdigit($hash)) {
            throw new InvalidArgumentException("Hash must be created from valid hexadecimal strings");
        }

        if (strlen($hash) % 2 !== 0) {
            throw new InvalidArgumentException("Hash must be created from an even length hexadecimal string");
        }

        $bytes = hex2bin($hash);

        if ($bytes === false) {
            throw new RuntimeException("Failed to convert hex to binary");
        }

        return new self($bytes);
    }

    /**
     * Create hash from concatinated bit string or array of bits.
     *
     * @param array<int|string|bool> $hash
     */
    public static function fromBits(string|array $hash): self
    {
        $hash = is_string($hash) ? str_split($hash) : $hash;

        if (count($hash) < 8) {
            throw new InvalidArgumentException('Hash must be created from at least 8 bits');
        }

        $bits = array_map(fn(mixed $bit): string => (string) new BitParser($bit), $hash);
        $bytes = array_map(fn(array $bits): string => implode('', $bits), array_chunk($bits, 8));
        $bytes = array_map(fn(string $byte): string => pack('C', bindec($byte)), $bytes);

        return new self(implode('', $bytes));
    }

    /**
     * Create hash from given byte string.
     */
    public static function fromBytes(string $hash): self
    {
        return new self($hash);
    }

    /**
     * Create hash from a base64-encoded string.
     */
    public static function fromBase64(string $hash): self
    {
        $hash = base64_decode($hash, strict: true);

        if ($hash === false) {
            throw new InvalidArgumentException('Unable to base64-decode string');
        }

        return new self($hash);
    }

    /**
     * {@inheritdoc}
     *
     * @see HashInterface::toHex()
     */
    public function toHex(): string
    {
        $bytes = str_split($this->bytes);
        $bytes = array_map(fn(string $byte): string => dechex(ord($byte)), $bytes);
        $bytes = array_map(fn(string $byte): string => str_pad($byte, 2, '0', STR_PAD_LEFT), $bytes);

        return implode('', $bytes);
    }

    /**
     * {@inheritdoc}
     *
     * @see HashInterface::toBits()
     */
    public function toBits(): string
    {
        $bytes = str_split($this->bytes);
        $bytes = array_map(fn(string $byte): string => decbin(ord($byte)), $bytes);
        $bytes = array_map(fn(string $byte): string => str_pad($byte, 8, '0', STR_PAD_LEFT), $bytes);
        $bytes = array_map(fn(string $byte): array => str_split($byte), $bytes);

        return implode('', array_merge(...$bytes));
    }

    /**
     * {@inheritdoc}
     *
     * @see HashInterface::toBytes()
     */
    public function toBytes(): string
    {
        return $this->bytes;
    }

    /**
     * {@inheritdoc}
     *
     * @see HashInterface::toBase64()
     */
    public function toBase64(): string
    {
        return base64_encode($this->bytes);
    }

    /**
     * {@inheritdoc}
     *
     * @see HashInterface::distance()
     */
    public function distance(HashInterface $hash): int
    {
        if ($this->bitLength() !== $hash->bitLength()) {
            throw new InvalidArgumentException("Hashes must have the same bit length for comparison");
        }

        $bits1 = $this->toBits();
        $bits2 = $hash->toBits();

        if (extension_loaded('gmp') && function_exists('gmp_hamdist')) {
            return gmp_hamdist('0b1' . $bits1, '0b1' . $bits2);
        }

        return count(array_diff_assoc(str_split($bits1), str_split($bits2)));
    }

    /**
     * {@inheritdoc}
     *
     * @see HashInterface::equals()
     */
    public function equals(HashInterface $hash, int $leeway = 0): bool
    {
        return $this->distance($hash) <= $leeway;
    }

    /**
     * {@inheritdoc}
     *
     * @see HashInterface::bitLength()
     */
    public function bitLength(): int
    {
        return strlen($this->bytes) * 8;
    }

    /**
     * {@inheritdoc}
     *
     * @see JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize(): mixed
    {
        return $this->toHex();
    }

    /**
     * {@inheritdoc}
     *
     * @see Stringable::__toString()
     */
    public function __toString(): string
    {
        return $this->toHex();
    }

    /**
     * Display debug info of current hash.
     *
     * @return array<mixed>
     */
    public function __debugInfo(): array
    {
        return [
            'hex' => $this->toHex(),
            'bitLength' => $this->bitLength(),
        ];
    }
}
