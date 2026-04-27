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
        if (strlen($this->bytes) === 0) {
            throw new InvalidArgumentException("Hash must be a non-empty string");
        }
    }

    /**
     * Create hash from hexadecimal string.
     */
    public static function fromHex(string $hash): self
    {
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
     * Create hash from concatinated bit string or array of bits.
     *
     * @param array<int|string|bool> $hash
     */
    public static function fromBits(string|array $hash): self
    {
        $hash = is_string($hash) ? str_split($hash) : $hash;

        if (count($hash) === 0) {
            throw new InvalidArgumentException('Unable to create hash from empty array of bits.');
        }

        $bits = array_map(fn(mixed $bit): string => (string) new BitParser($bit), $hash);
        $bytes = array_map(fn(array $bits): string => implode('', $bits), array_chunk($bits, 8));
        $bytes = array_map(fn(string $byte): string => pack('C', bindec($byte)), $bytes);

        return new self(implode('', $bytes));
    }

    /**
     * Create hash from decimal string.
     */
    public static function fromDecimal(string $decimal): self
    {
        if ($decimal === '') {
            throw new InvalidArgumentException('Hash must be a non-empty string.');
        }

        if (!preg_match('/\A[0-9]+\z/', $decimal)) {
            throw new InvalidArgumentException('Hash must be a decimal string.');
        }

        $decimal = ltrim($decimal, '0');

        if ($decimal === '') {
            return self::fromBits('0');
        }

        if (extension_loaded('gmp') && function_exists('gmp_init') && function_exists('gmp_strval')) {
            return self::fromBits(gmp_strval(gmp_init($decimal, 10), 2));
        }

        $bits = '';

        while ($decimal !== '0') {
            $next = '';
            $carry = 0;
            $length = strlen($decimal);

            for ($index = 0; $index < $length; $index++) {
                $value = ($carry * 10) + (int) $decimal[$index];
                $digit = intdiv($value, 2);
                $carry = $value % 2;

                if ($digit !== 0 || $next !== '') {
                    $next .= (string) $digit;
                }
            }

            $bits = (string) $carry . $bits;
            $decimal = $next === '' ? '0' : $next;
        }

        return self::fromBits($bits);
    }

    /**
     * Create hash from given byte string.
     */
    public static function fromBytes(string $hash): self
    {
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
    public function toBits(): array
    {
        $bytes = str_split($this->bytes);
        $bytes = array_map(fn(string $byte): string => decbin(ord($byte)), $bytes);
        $bytes = array_map(fn(string $byte): string => str_pad($byte, 8, '0', STR_PAD_LEFT), $bytes);
        $bytes = array_map(fn(string $byte): array => str_split($byte), $bytes);

        return array_merge(...$bytes);
    }

    /**
     * {@inheritdoc}
     *
     * @see HashInterface::toDecimal()
     */
    public function toDecimal(): string
    {
        $bits = implode('', $this->toBits());

        if (extension_loaded('gmp') && function_exists('gmp_init') && function_exists('gmp_strval')) {
            return gmp_strval(gmp_init($bits, 2), 10);
        }

        $decimal = '0';
        $length = strlen($bits);

        for ($index = 0; $index < $length; $index++) {
            $keep = (int) $bits[$index];
            for ($pos = strlen($decimal) - 1; $pos >= 0; $pos--) {
                $value = ((int) $decimal[$pos] * 2) + $keep;
                $decimal[$pos] = (string) ($value % 10);
                $keep = intdiv($value, 10);
            }

            if ($keep > 0) {
                $decimal = (string) $keep . $decimal;
            }
        }

        return $decimal;
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
        ];
    }
}
