<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Map
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2011-2015, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Map;

use \Fisharebest\Webtrees\Database;

/**
 * Maps provider for GoogleMaps™.
 * Based on the MapProvider interface, and the core Google Maps module.
 * 
 * @uses MapProviderInterface
 */
class GoogleMapsProvider implements MapProviderInterface {	
	
	/**
	 * {@inheritdoc }
	 * @see \MyArtJaub\Webtrees\Map\MapProviderInterface::getProviderPlaceId()
	 */
	public function getProviderPlaceId(\Fisharebest\Webtrees\Place $place) {
		if(!$place->isEmpty()) {
			$parent = array_reverse( explode (',', $place->getGedcomName()));
			$place_id = 0;
			for ($i=0; $i<count($parent); $i++) {
				$parent[$i] = trim($parent[$i]);
				if (empty($parent[$i])) $parent[$i]='unknown';// GoogleMap module uses "unknown" while GEDCOM uses , ,
				$pl_id=Database::prepare('SELECT pl_id FROM `##placelocation` WHERE pl_level=? AND pl_parent_id=? AND pl_place LIKE ? ORDER BY pl_place')
					->execute(array($i, $place_id, $parent[$i]))
					->fetchOne();
				if (empty($pl_id)) break;
				$place_id = $pl_id;
			}
			return $place_id;
		}
		return null;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \MyArtJaub\Webtrees\Map\MapProviderInterface::getPlaceIcon()
	 */
	public function getPlaceIcon(\Fisharebest\Webtrees\Place $place) {
		if(!$place->isEmpty()){
			$place_details =
				Database::prepare("SELECT SQL_CACHE pl_icon FROM `##placelocation` WHERE pl_id=? ORDER BY pl_place")	
				->execute(array($this->getProviderPlaceId($place)))
				->fetchOneRow();
			if($place_details){
				return WT_MODULES_DIR.'googlemap/'.$place_details->pl_icon;
			}
		}
		return null;		
	}

}