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
    private function __construct(readonly protected string $bytes)
    {
        if (strlen($this->bytes) === 0) {
            throw new InvalidArgumentException("Unable to create hash from empty string");
        }
    }

    /**
     * Create hash from hexadecimal string.
     */
    public static function fromHex(string $hash): self
    {
        if ($hash === '') {
            throw new InvalidArgumentException('Hash must be a non-empty hexadecimal string.');
        }

        $hash = strtolower($hash);

        if (!ctype_xdigit($hash)) {
            throw new InvalidArgumentException("Hash must be a valid hexadecimal string");
        }

        if (strlen($hash) % 2 !== 0) {
            throw new InvalidArgumentException("Hash must be a even length hexadecimal string");
        }

        $bytes = hex2bin($hash);

        if ($bytes === false) {
            throw new RuntimeException("Failed to convert hex to binary");
        }

        return new self($bytes);
    }

    /**
     * {@inheritdoc}
     *
     * @see HashInterface::fromBase64()
     */
    public static function fromBase64(string $hash): self
    {
        $bytes = base64_decode($hash, strict: true);

        if ($bytes === false) {
            throw new RuntimeException("Failed to decode base64 to binary");
        }

        return new self($bytes);
    }

    /**
     * {@inheritdoc}
     *
     * @see HashInterface::distance()
     */
    public function distance(HashInterface $hash): int
    {
        if ($this->bitLength() !== $hash->bitLength()) {
            throw new InvalidArgumentException("Hashes must be of equal length");
        }

        $bits1 = $this->toBits();
        $bits2 = $hash->toBits();

        if (extension_loaded('gmp') && function_exists('gmp_hamdist')) {
            return gmp_hamdist('0b1' . implode('', $bits1), '0b1' . implode('', $bits2));
        }

        return count(array_diff_assoc($bits1, $bits2));
    }

    public function distanceNormalized(HashInterface $hash): float
    {
        return $this->distance($hash) / $this->bitLength();
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

    public function toHex(): string
    {
        return bin2hex($this->bytes);
    }

    public function toBase64(): string
    {
        return base64_encode($this->bytes);
    }

    protected function toBits(): array
    {
        $bits = [];
        $length = strlen($this->bytes);

        for ($i = 0; $i < $length; $i++) {
            $byte = ord($this->bytes[$i]);
            for ($bit = 7; $bit >= 0; $bit--) {
                $bits[] = ($byte >> $bit) & 1;
            }
        }

        return $bits;
    }


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
        ];
    }
}
