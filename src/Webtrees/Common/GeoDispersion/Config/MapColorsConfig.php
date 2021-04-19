<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage GeoDispersion
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2021, Jonathan Jaubart
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
    private Color $default, $stroke, $max_value, $hover;
    
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
    
    function defaultColor(): Color
    {
        return $this->default;
    }
    
    function strokeColor(): Color
    {
        return $this->stroke;
    }
    
    function minValueColor(): Color
    {
        return new Rgb(255, 255, 255);
    }
    
    function maxValueColor(): Color
    {
        return $this->max_value;
    }
    
    function hoverColor(): Color
    {
        return $this->hover;
    }

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