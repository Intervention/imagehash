<?php

declare(strict_types=1);

namespace Intervention\ImageHash;

use Intervention\Image\Exceptions\InvalidArgumentException;
use Intervention\ImageHash\Interfaces\HashInterface;
use JsonSerializable;
use Stringable;

class Hash implements HashInterface, Stringable, JsonSerializable
{
    private const int BITS_PER_BYTE = 8;
    private const int HEX_DIGITS_PER_BYTE = 2;

    /**
     * @param array<int> $bytes
     * @throws InvalidArgumentException
     */
    private function __construct(protected array $bytes)
    {
        if (count($bytes) === 0) {
            throw new InvalidArgumentException('Unable to create hash from emtpy array of bytes.');
        }

        foreach ($this->bytes as $byte) {
            if (!is_int($byte) || $byte < 0 || $byte > 255) {
                throw new InvalidArgumentException('Hash bytes must be integers between 0 and 255.');
            }
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

        if (!preg_match('/\A[0-9a-fA-F]+\z/', $hash)) {
            throw new InvalidArgumentException('Hash must be a hexadecimal string.');
        }

        if (strlen($hash) % self::HEX_DIGITS_PER_BYTE !== 0) {
            $hash = '0' . $hash;
        }

        return new self(array_map('hexdec', str_split($hash, self::HEX_DIGITS_PER_BYTE)));
    }

    /**
     * Create hash from concatinated bit string or array of bits.
     *
     * @param string|array<int|string|bool> $hash
     */
    public static function fromBits(string|array $hash): self
    {
        if (is_array($hash)) {
            if (count($hash) === 0) {
                throw new InvalidArgumentException('Unable to create hash from emtpy array of bytes.');
            }

            // normalize array to concatinated bit string
            $hash = implode('', array_map(fn(mixed $bit) => new BitParser($bit), $hash));
        }

        if ($hash === '') {
            throw new InvalidArgumentException('Hash must be a non-empty string.');
        }

        if (!preg_match('/\A[01]+\z/', $hash)) {
            throw new InvalidArgumentException('Hash must contain only bits ("0" or "1").');
        }

        $length = (int) ceil(strlen($hash) / self::BITS_PER_BYTE) * self::BITS_PER_BYTE;
        $hash = str_pad($hash, $length, '0', STR_PAD_LEFT);

        return new self(array_map('bindec', str_split($hash, self::BITS_PER_BYTE)));
    }

    /**
     * Create hash from decimal string.
     */
    public static function fromDecimal(int|string $decimal): self
    {
        $decimal = (string) $decimal;

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
     * Create hash from given utf-8 string.
     */
    public static function fromUtf8(string $hash): self
    {
        if ($hash === '') {
            throw new InvalidArgumentException('Hash must be a non-empty string.');
        }

        return new self(array_map('ord', str_split($hash)));
    }

    /**
     * {@inheritdoc}
     *
     * @see HashInterface::toHex()
     */
    public function toHex(): string
    {
        return join('', array_map(function (int $byte): string {
            return str_pad(dechex($byte), self::HEX_DIGITS_PER_BYTE, '0', STR_PAD_LEFT);
        }, $this->bytes));
    }

    /**
     * {@inheritdoc}
     *
     * @see HashInterface::toBits()
     */
    public function toBits(): string
    {
        return join('', array_map(function (int $byte): string {
            return str_pad(decbin($byte), self::BITS_PER_BYTE, '0', STR_PAD_LEFT);
        }, $this->bytes));
    }

    /**
     * {@inheritdoc}
     *
     * @see HashInterface::toDecimal()
     */
    public function toDecimal(): string
    {
        $bits = ltrim($this->toBits(), '0');

        if ($bits === '') {
            return '0';
        }

        if (extension_loaded('gmp') && function_exists('gmp_init') && function_exists('gmp_strval')) {
            $gmpInit = 'gmp_init';
            $gmpStrval = 'gmp_strval';

            return $gmpStrval($gmpInit($bits, 2), 10);
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
     * @see HashInterface::toUtf8()
     */
    public function toUtf8(): string
    {
        return join('', array_map('chr', $this->bytes));
    }

    /**
     * {@inheritdoc}
     *
     * @see HashInterface::distance()
     */
    public function distance(HashInterface $hash): int
    {
        $bits1 = $this->toBits();
        $bits2 = $hash->toBits();

        if (extension_loaded('gmp') && function_exists('gmp_hamdist')) {
            $gmpHamdist = 'gmp_hamdist';

            return $gmpHamdist('0b' . $bits1, '0b' . $bits2);
        }

        // normalize bit strings to same length
        $length = max(strlen($bits1), strlen($bits2));
        $bits1 = str_pad($bits1, $length, '0', STR_PAD_LEFT);
        $bits2 = str_pad($bits2, $length, '0', STR_PAD_LEFT);

        return count(array_diff_assoc(str_split($bits1), str_split($bits2)));
    }

    /**
     * {@inheritdoc}
     *
     * @see HashInterface::equals()
     */
    public function equals(HashInterface $hash): bool
    {
        return $this->distance($hash) === 0;
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
            $this->toHex(),
            $this->toBits(),
            $this->bytes
        ];
    }
}
