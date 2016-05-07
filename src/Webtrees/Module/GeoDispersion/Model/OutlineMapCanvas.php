<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage GeoDispersion
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module\GeoDispersion\Model;

/**
 * Decsribe an Outline Map Canvas
 */
class OutlineMapCanvas {
    
	/**
	 * Width of the map (in pixels).
	 * @var int $width
	 */
    public $width;
	
	/**
	 * Height of the map (in pixels).
	 * @var int $height
	 */
    public $height;
	
	/**
	 * Background color for the map shapes with values (when no transparency if applied).
	 * @var string $max_color
	 */
    public $max_color;
	
	/**
	 * Background color for the map shapes when hovered.
	 * @var string $hover_color
	 */
    public $hover_color;
	
	/**
	 * Background color for the map.
	 * @var string $background_color
	 */
    public $background_color;
	
	/**
	 * Border color for the map.
	 * @var string $background_stroke
	 */
    public $background_stroke;
	
	/**
	 * Default color for the shapes (without any value).
	 * @var string $default_color
	 */
    public $default_color;
	
	/**
	 * Default border color for the shapes.
	 * @var string $default_stroke
	 */
    public $default_stroke;
    
	/**
	 * Constructor for OutlineMapCanvas.
	 * 
	 * @param int $width Map width
	 * @param int $height Map height
	 * @param string $max_color Background color for shapes with values	 
	 * @param string $hover_color Background color for shapes with values, when hovered
	 * @param string $background_color Background color for the map	 
	 * @param string $background_stroke Border color for the map	 
	 * @param string $default_color Default background color for the shapes
	 * @param string $default_stroke Default border color for the shapes
	 */
    public function __construct(
        $width,
        $height,
        $max_color,
        $hover_color,
        $background_color,
        $background_stroke,
        $default_color,
        $default_stroke
     ) {
        $this->width = $width;
        $this->height = $height;
        $this->max_color = $max_color;
        $this->hover_color = $hover_color;
        $this->background_color = $background_color;
        $this->background_stroke = $background_stroke;
        $this->default_color = $default_color;
        $this->default_stroke = $default_stroke;
    }
    
    
}
 