<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage PatronymicLineage
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module\PatronymicLineage\Model;

use Fisharebest\Webtrees\Place;
/**
 * Derived class from the LineageNode, indicating a Root Lineage node
 */
class LineageRootNode extends LineageNode {
	
	/**
	 * @var array $places Places for the lineage node
	 */
	protected $places;
	
	/**
	 * Constructor for LineageRootNode
	 * 
	 * @param Fisharebest\Webtrees\Individual $node_indi
	 */
	public function __construct($node_indi = null) {
		parent::__construct($node_indi, $this);
		$this->places = array();
	}
	
	/**
	 * Adds a place to the list of lineage's place
	 * 
	 * @param Place $place
	 */
	public function addPlace(Place $place) {
		if(!is_null($place) && !$place->isEmpty()){
			$place_name = $place->getFullName();
			if(isset($this->places[$place_name])){
				$this->places[$place_name]+=1;
			}
			else{
				$this->places[$place_name] = 1;
			}
		}
	}
	
	/**
	 * Returns the list of place for the lineage
	 * 
	 * @return array
	 */
	public function getPlaces() {
		ksort($this->places);
		return $this->places;
	}
	
}
 