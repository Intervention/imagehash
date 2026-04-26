# Intervention ImageHash
## Perceptual image hashing for PHP

[![Latest Version](https://img.shields.io/packagist/v/intervention/imagehash.svg)](https://packagist.org/packages/intervention/imagehash)
[![Build Status](https://github.com/Intervention/image/actions/workflows/run-tests.yml/badge.svg)](https://github.com/Intervention/imagehash/actions)
[![Monthly Downloads](https://img.shields.io/packagist/dm/intervention/imagehash.svg)](https://packagist.org/packages/intervention/imagehash/stats)
[![Support me on Ko-fi](https://raw.githubusercontent.com/Intervention/image/develop/.github/images/support.svg)](https://ko-fi.com/interventionphp)

> A perceptual hash is a fingerprint of a multimedia file derived from various features from its content. Unlike cryptographic hash functions which rely on the avalanche effect of small changes in input leading to drastic changes in the output, perceptual hashes are "close" to one another if the features are similar.

Perceptual hashes are a different concept compared to cryptographic hash functions like MD5 and SHA1. With cryptographic hashes, the hash values are random. The data used to generate the hash acts like a random seed, so the same data will generate the same result, but different data will create different results. Comparing two SHA1 hash values really only tells you two things. If the hashes are different, then the data is different. And if the hashes are the same, then the data is likely the same. In contrast, perceptual hashes can be compared -- giving you a sense of similarity between the two data sets.

## Installation

*This package has not reached a stable version yet, backwards compatibility may be broken between 0.x releases. Make sure to lock your version if you intend to use this in production!*

Install using composer:

```bash
composer require intervention/imagehash
```

## Usage

The library comes with 4 built-in hashing strategies:

 - `Intervention\ImageHash\Strategies\Average` - Hash based the average image color
 - `Intervention\ImageHash\Strategies\Difference` - Hash based on the previous pixel
 - `Intervention\ImageHash\Strategies\Block` - Hash based on blockhash.io
 - `Intervention\ImageHash\Strategies\Perceptual` - The original pHash

Choose one of these strategies. If you don't know which one to use, try the `Difference` strategy. Some strategies allow some configuration, be sure to check the constructor.

```php
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\ImageHash\ImageHasher;
use Intervention\ImageHash\Strategies\Difference;

$hasher = new ImageHasher(new Driver(), new Difference());
$hash = $hasher->hash('path/to/image.jpg');

echo $hash;
// or
echo $hash->toHex();
```

The resulting `Hash` object, is a hexadecimal image fingerprint that can be stored once calculated. Two fingerprints can be compared by the hamming distance for similarities. Low distance values will indicate that the images are similar or the same, high distance values indicate that the images are different. Use the following methods for comparisons:

```php
$distance = $hash1->distance($hash2); // 12
$equals = $hash1->equals($hash2); // false
```

A perceptual hash is a compact summary of visual features. Because of that, both the input images and the processing strategy influence the result. A perceptual hash and its distances to other hashes may vary depending on the image data, hashing strategy, strategy options and processing pipeline.

The `Hash` object can return the internal binary hash in a couple of different format:

```php
echo $hash->toHex(); // "74657374"
echo $hash->toBits(); // "01110100011001010111001101110100"
echo $hash->toUtf8(); // "test"
```

If you want to reconstruct a `Hash` object from a previous calculated value, use:

```php
$hash = Hash::fromHex('74657374');
$hash = Hash::fromBits('01110100011001010111001101110100');
$hash = Hash::fromUtf8('test');
```

## Requirements

 - PHP 8.3 or higher
 - The [gd](http://php.net/manual/en/book.image.php), [imagick](http://php.net/manual/en/book.imagick.php) or [libvips](https://www.libvips.org) extension
 - Optionally, install the [GMP](http://php.net/manual/en/book.gmp.php) extension for faster fingerprint comparisons

## Demo

These images are similar:

![Equals1](https://raw.githubusercontent.com/Intervention/imagehash/master/tests/images/tropical_high.jpg)
![Equals2](https://raw.githubusercontent.com/Intervention/imagehash/master/tests/images/tropical_watermark.jpg)

	Image 1 hash: 3c3e0e1a3a1e1e1e (0011110000111110000011100001101000111010000111100001111000011110)
	Image 2 hash: 3c3e0e3e3e1e1e1e (0011110000111110000011100011111000111110000111100001111000011110)
	Hamming distance: 3

These images are different:

![Equals1](https://raw.githubusercontent.com/Intervention/imagehash/master/tests/images/mountain_day.jpg)
![Equals2](https://raw.githubusercontent.com/Intervention/imagehash/master/tests/images/mountain_night.jpg)

	Image 1 hash: 69684858535b7575 (0010100010101000101010001010100010101011001010110101011100110111)
	Image 2 hash: e1e1e2a7bbaf6faf (0111000011110000111100101101001101011011011101010011010101001111)
	Hamming distance: 32

## Authors

This project is based on work originally developed by [Jens Segers](https://github.com/jenssegers) and released under the MIT License. Many thanks to him for sharing his work with the community, which made this fork possible.

This version is maintained by [Oliver Vogel](https://intervention.io) including modifications and improvements, but it builds directly on the solid foundation Jens created. 

## License

Intervention ImageHash is licensed under the [MIT License](LICENSE).
