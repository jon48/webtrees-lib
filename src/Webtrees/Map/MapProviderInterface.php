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

/**
 * Interface for provider of maps data.
 */
interface MapProviderInterface {
	
	/**
	 * Returns the identifier of the a place, as recorded in the map provider.
	 * 
	 * @param \Fisharebest\Webtrees\Place $place Place to identify
	 * @return int|string|null Place identifier 
	 */
	public function getProviderPlaceId(\Fisharebest\Webtrees\Place $place);

	/**
	 * Returns the reference to an icon representing a place.
	 * 
	 * @param \Fisharebest\Webtrees\Place $place Place to identify
	 * @return string|null Place icon reference
	 */
	public function getPlaceIcon(\Fisharebest\Webtrees\Place $place);
	
}
