<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage GeoDispersion
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2021-2023, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Common\GeoDispersion\Config;

use Spatie\Color\Color;
use Spatie\Color\Rgb;
use JsonSerializable;

/**
 * Configuration for map colors
 */
class MapColorsConfig implements JsonSerializable
{
    private Color $default;
    private Color $stroke;
    private Color $max_value;
    private Color $hover;

    /**
     * Constructor for MapColorsConfig
     *
     * @param Color $default
     * @param Color $stroke
     * @param Color $max_value
     * @param Color $hover
     */
    public function __construct(
        Color $default,
        Color $stroke,
        Color $max_value,
        Color $hover
    ) {
        $this->default = $default;
        $this->stroke = $stroke;
        $this->max_value = $max_value;
        $this->hover = $hover;
    }

    /**
     * Get the default color for the features
     *
     * @return Color
     */
    public function defaultColor(): Color
    {
        return $this->default;
    }

    /**
     * Get the color for the features' strokes
     *
     * @return Color
     */
    public function strokeColor(): Color
    {
        return $this->stroke;
    }

    /**
     * Get the color for the features with the lowest count
     *
     * @return Color
     */
    public function minValueColor(): Color
    {
        return new Rgb(255, 255, 255);
    }

    /**
     * Get the color for the features with the highest count
     *
     * @return Color
     */
    public function maxValueColor(): Color
    {
        return $this->max_value;
    }

    /**
     * Get the color for feature hovering
     *
     * @return Color
     */
    public function hoverColor(): Color
    {
        return $this->hover;
    }

    /**
     * {@inheritDoc}
     * @see JsonSerializable::jsonSerialize()
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'default'   => (string) $this->defaultColor()->toHex(),
            'stroke'    => (string) $this->strokeColor()->toHex(),
            'maxvalue'  => (string) $this->maxValueColor()->toHex(),
            'hover'     => (string) $this->hoverColor()->toHex(),
        ];
    }
}
