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

use Fisharebest\Webtrees\I18N;
use MyArtJaub\Webtrees\Constants;

/**
 * Describe an Outline Map
 */
class OutlineMap {
    
	/**
	 * Name of the file containing the description of the map.
	 * @var string $filename
	 */
    protected $filename;
    
	/**
	 * Indicates whether the description has been loaded from the file.
	 * @var bool $is_loaded
	 */
    protected $is_loaded;
    
	/**
	 * Description/title of the map.
	 * @var string $description
	 */
    protected $description;
    
	/**
	 * Name(s) of the parent level(s) of the map.
	 * @var string $is_loaded
	 */
    protected $top_level_name;
    
    /**
     * Map canvas
     * @var OutlineMapCanvas $canvas
     */
    protected $canvas;
    
    /**
     * Map subdivisions
     * @var array $subdivisions
     */
    protected $subdivisions;
    
    /**
     * Places mappings
     * @var array $subdivisions
     */
    protected $mappings;
    
    /**
     * Constructor for GeoAnalysisMap.
     *
     * @param string $filename Outline map file name
     * @param bool $load Should the map be loaded immediately
     */
    public function __construct($filename, $load = false) {
        $this->filename = $filename;
        $this->is_loaded = false;
        $this->subdivisions = array();
        $this->mappings = array();
        if($load) $this->load();
    }
    
    /**
     * Load the map settings contained within its XML representation
     *
     * XML structure :
     * 	- displayName : Display name of the map
     * 	- topLevel : Values of the top level subdivisions (separated by commas, if multiple)
     * 	- canvas : all settings related to the map canvas.
     * 		- width : canvas width, in px
     * 		- height : canvas height, in px
     * 		- maxcolor : color to identify places with ancestors, RGB hexadecimal
     * 		- hovercolor : same as previous, color when mouse is hovering the place, RGB hexadecimal
     * 		- bgcolor : map background color, RGB hexadecimal
     * 		- bgstroke : map stroke color, RGB hexadecimal
     * 		- defaultcolor : default color of places, RGB hexadecimal
     * 		- defaultstroke : default stroke color, RGB hexadecimal
     * 	- subdvisions : for each subdivision :
	 *		- id : Subdivision id, must be compatible with PHP variable constraints, and unique
     * 		- name: Display name for the place
	 *		- parent: if any, describe to which parent level the place if belonging to
     * 		- <em>Element value<em> : SVG description of the subdvision shape
	 *	- mapping : for each subdivision :
	 *		- name : Name of the place to map
     * 		- mapto: Name of the place to map to
	 * 
     */
    protected function load() {
        if(file_exists(WT_ROOT.WT_MODULES_DIR.Constants::MODULE_MAJ_GEODISP_NAME.'/maps/'.$this->filename)){
            $xml = simplexml_load_file(WT_ROOT.WT_MODULES_DIR.Constants::MODULE_MAJ_GEODISP_NAME.'/maps/'.$this->filename);
            if($xml){
                $this->description = trim($xml->displayName);
                $this->top_level_name = trim($xml->topLevel);
                $this->canvas = new OutlineMapCanvas(
                    trim($xml->canvas->width),
                    trim($xml->canvas->height), 
                    trim($xml->canvas->maxcolor), 
                    trim($xml->canvas->hovercolor), 
                    trim($xml->canvas->bgcolor),
                    trim($xml->canvas->bgstroke),
                    trim($xml->canvas->defaultcolor), 
                    trim($xml->canvas->defaultstroke)
                );
                foreach($xml->subdivisions->children() as $subdivision){
                    $attributes = $subdivision->attributes();
                    $key = I18N::strtolower(trim($attributes['name']));
                    if(isset($attributes['parent'])) $key .= '@'. I18N::strtolower(trim($attributes['parent']));
                    $this->subdivisions[$key] = array(
                        'id' => trim($attributes['id']),
                        'displayname' => trim($attributes['name']),
                        'coord' => trim($subdivision[0])
                    );
                }
                if(isset($xml->mappings)) {
                    foreach($xml->mappings->children() as $mappings){
                        $attributes = $mappings->attributes();
                        $this->mappings[I18N::strtolower(trim($attributes['name']))] = I18N::strtolower(trim($attributes['mapto']));
                    }
                }
                $this->is_loaded = true;
                return;
            }
        }
        throw new \Exception('The Outline Map could not be loaded from XML.');
    }
    
    /**
     * Get the status of the map loading from the XML file.
     * 
     * @return bool
     */
    public function isLoaded() {
        try{
            if(!$this->is_loaded) $this->load();
        }
        catch (\Exception $ex) { }
        return $this->is_loaded;
    }
    
	/**
	 * Get the map file name.
	 * @return string
	 */
    public function getFileName() {
        return $this->filename;
    }
    
	/**
	 * Get the map file name.
	 * @return string
	 */
    public function getDescription() {
        if(!$this->is_loaded) $this->load();
        return $this->description;
    }
    
	/**
	 * Get the name of the map parent level. 
	 * @return string
	 */
    public function getTopLevelName() {
        if(!$this->is_loaded) $this->load();
        return $this->top_level_name;
    }    
    
    /**
     * Get the Outline Map canvas.
     * @return \MyArtJaub\Webtrees\Module\GeoDispersion\Model\OutlineMapCanvas
     */
    public function getCanvas() {
        if(!$this->is_loaded) $this->load();
        return $this->canvas;
    }
    
	/**
     * Get the subdivisions of the map.
     * @return array
     */
    public function getSubdivisions() {
        if(!$this->is_loaded) $this->load();
        return $this->subdivisions;
    }
    
	/**
     * Get the places mappings of the map.
     * @return array
     */
    public function getPlacesMappings() {
        if(!$this->is_loaded) $this->load();
        return $this->mappings;
    }
    
}
 