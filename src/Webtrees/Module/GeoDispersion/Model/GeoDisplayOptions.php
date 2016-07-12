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
 * Class holding options for displaying GeoAnalysis.
 */
class GeoDisplayOptions {
    
    /**
	 * Outline map to be used for diaplay
     * @var (null|OutlineMap) $map
     */
    protected $map;
    
	/**
	 * Level in the Gedcom hierarchy of the parent level of the outline map.
	 * @var int $map_level
	 */
    protected $map_level;
    
	/**
	 * Option to use flags in places display, instead of or in addition to the name.
	 * @var bool $use_flags
	 */
    protected $use_flags;
    
	/**
	 * Option to define the number of top places to display in the generation view.
	 * @var int $max_details_in_gen
	 */
    protected $max_details_in_gen;
    
    /**
	 * Get the outline map to use for display.
	 *
     * @return (OutlineMap|null)
     */
    public function getMap(){
        return $this->map;
    }
    
	/**
	 * Set the outline map to use for display.
	 *
     * @param (OutlineMap|null) $map
	 * @return self Enable method-chaining
     */
    public function setMap(OutlineMap $map = null) {
        $this->map = $map;
        return $this;
    }
    
	/**
	 * Get the level of the map parent place
	 *
	 * @return int
	 */
    public function getMapLevel(){
        return $this->map_level;
    }
    
	/**
	 * Set the level of the map parent place
	 *
	 * @param int $maplevel
	 * @return self Enable method-chaining
	 */
    public function setMapLevel($maplevel) {
        $this->map_level = $maplevel;
        return $this;
    }
    
	/**
	 * Get whether flags should be used in places display
	 *
	 * @return bool
	 */
    public function isUsingFlags(){
        return $this->use_flags;
    }
    
	/**
	 * Set whether flags should be used in places display
	 *
	 * @param bool $use_flags
	 * @return self Enable method-chaining
	 */
    public function setUsingFlags($use_flags) {
        $this->use_flags = $use_flags;
        return $this;
    }
    
	/**
	 * Get the number of Top Places in the generation view
	 *
	 * @return int
	 */
    public function getMaxDetailsInGen(){
        return $this->max_details_in_gen;
    }
    
	/**
	 * Set the number of Top Places in the generation view
	 *
	 * @param int $max_details_in_gen
	 * @return self Enable method-chaining
	 */
    public function setMaxDetailsInGen($max_details_in_gen) {
        $this->max_details_in_gen = $max_details_in_gen;
        return $this;
    }
                   
}
 