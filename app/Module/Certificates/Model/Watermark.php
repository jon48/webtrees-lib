<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Sosa
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2020, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\Certificates\Model;

/**
 * Model class for watermark to be applied to a certificate image.
 */
class Watermark
{
    /**
     * Default font color for watermarks
     * @var string DEFAULT_COLOR
     * */
    public const DEFAULT_COLOR = '#4D6DF3';

    /**
     * Default maximum font size for watermarks
     * @var int DEFAULT_SIZE
     * */
    public const DEFAULT_SIZE = 18;

    /**
     * @var string $text
     */
    private $text;

    /**
     * @var string $color;
     */
    private $color;


    /**
     * @var int $size
     */
    private $size;

    /**
     * Constructor for Watermark data class
     *
     * @param string $text
     * @param string $color
     * @param int $size
     */
    public function __construct(string $text, string $color, int $size)
    {
        $this->text = $text;
        $this->color = $color;
        $this->size = $size;
    }

    /**
     * Get the watermark text.
     *
     * @return string
     */
    public function text(): string
    {
        return $this->text;
    }

    /**
     * Get the watermark font color.
     *
     * @return string
     */
    public function color(): string
    {
        return $this->color;
    }

    /**
     * Get the watermark maximum font size.
     * @return int
     */
    public function size(): int
    {
        return $this->size;
    }

    /**
     * Return an estimate of the size in pixels of the watermark text length.
     *
     * @return int
     */
    public function textLengthEstimate(): int
    {
        return $this->stringLengthEstimate(mb_strlen($this->text), $this->size);
    }

    /**
     * Decrease the font size if necessary, based on the image width.
     *
     * @param int $width
     */
    public function adjustSize(int $width): void
    {
        $len = mb_strlen($this->text);
        while ($this->stringLengthEstimate($len, $this->size) > 0.9 * $width) {
            $this->size--;
            if ($this->size == 2) {
                return;
            }
        }
    }

    /**
     * Return an estimate of the size in pixels of a text in a specified font size.
     *
     * @param int $text_length
     * @param int $font_size
     * @return int
     */
    private function stringLengthEstimate(int $text_length, int $font_size): int
    {
        return $text_length * (int) ceil(($font_size + 2) * 0.5);
    }
}
