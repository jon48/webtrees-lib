<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2012-2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Tree;

/**
 * Decorator class to extend native webtrees Place class.
 * 
 * @see \Fisharebest\Webtrees\Place
 */
class Place {

	/** @var \Fisharebest\Webtrees\Place Underlying base Place */
	protected $place;

	/**
	 * Contructor for the decorator
	 *
	 * @param \Fisharebest\Webtrees\Place $place_in The Place to extend
	 */
	public function __construct(\Fisharebest\Webtrees\Place $place){
		$this->place = $place;
	}

	/**
	 * 
	 * Returns an instance of \MyArtJaub\Webtrees\Place, based on the string provided.
	 *
	 * @param string $place_str
	 * @param \Fisharebest\Webtrees\Tree $tree
	 * @return \MyArtJaub\Webtrees\Place|null Instance of \MyArtJaub\Webtrees\Place, if relevant
	 */
	public static function getIntance($place_str, Tree $tree){
		$dplace = null;
		if(is_string($place_str) && strlen($place_str) > 0){
			$dplace = new Place(new \Fisharebest\Webtrees\Place($place_str, $tree));
		}
		return $dplace;
	}
	
	/**
	 * Return the native place record embedded within the decorator
	 *
	 * @return \Fisharebest\Webtrees\Place Embedded place record
	 */
	public function getDerivedPlace(){
		return $this->place;
	}
	
	/**
	 * Return HTML code for the place formatted as requested.
	 * The format string should used %n with n to describe the level of division to be printed (in the order of the GEDCOM place).
	 * For instance "%1 (%2)" will display "Subdivision (Town)".
	 *
	 * @param string $format Format for the place
	 * @param bool $anchor Option to print a link to placelist
	 * @return string HTML code for formatted place
	 */
	public function htmlFormattedName($format, $anchor = false){		
		$html='';
		
		$levels = array_map('trim', explode(',', $this->place->getGedcomName()));
		$nbLevels = count($levels);
		$displayPlace = $format;
		preg_match_all('/%[^%]/', $displayPlace, $matches);
		foreach ($matches[0] as $match2) {
			$index = str_replace('%', '', $match2);
			if(is_numeric($index) && $index >0 && $index <= $nbLevels){
				$displayPlace = str_replace($match2, $levels[$index-1] , $displayPlace);
			}
			else{
				$displayPlace = str_replace($match2, '' , $displayPlace);
			}
		}
		if ($anchor && !Auth::isSearchEngine()) {
			$html .='<a href="' . $this->place->getURL() . '">' . $displayPlace . '</a>';
		} else {
			$html .= $displayPlace;
		}
		
		return $html;
		
	}

}


?>