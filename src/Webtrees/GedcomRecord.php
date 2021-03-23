<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2011-2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

namespace MyArtJaub\Webtrees;

use MyArtJaub\Webtrees\Globals;

/**
 * Decorator class to extend native webtrees GedcomRecord class.
 * 
 * @see \Fisharebest\Webtrees\GedcomRecord
 */
class GedcomRecord {

	/** @var \Fisharebest\Webtrees\GedcomRecord Underlying base GedcomRecord */
	protected $gedcomrecord;

	/**
	 * Contructor for the decorator
	 *
	 * @param \Fisharebest\Webtrees\GedcomRecord $gedcomrecord_in The GedcomRecord to extend
	 */
	public function __construct(\Fisharebest\Webtrees\GedcomRecord $gedcomrecord_in){
		$this->gedcomrecord = $gedcomrecord_in;
	}

	/**
	 * Return the native gedcom record embedded within the decorator
	 *
	 * @return \Fisharebest\Webtrees\GedcomRecord Embedded gedcom record
	 */
	public function getDerivedRecord(){
		return $this->gedcomrecord;
	}
	
	/**
	 * Return whether the record is a new addition (and not just a modification)
	 * 
	 * @return boolean True id new addition
	 */
	public function isNewAddition() {
		return $this->gedcomrecord->isPendingAddtion()
			&& $this->gedcomrecord->privatizeGedcom(\Fisharebest\Webtrees\Auth::PRIV_HIDE) === null;
	}
		
	/**
	 * Add additional options to the core formatFirstMajorFact function.
	 * If no option is suitable, it will try returning the core function.
	 *
	 * Option 10 : display <i>factLabel shortFactDate shortFactPlace</i>
	 *
	 * @uses \MyArtJaub\Webtrees\Functions\FunctionsPrint
	 * @param string $facts List of facts to find information from
	 * @param int $style Style to apply to the information. Number >= 10 should be used in this function, lower number will return the core function.
	 * @return string Formatted fact description
	 */
	public function formatFirstMajorFact($facts, $style) {
		foreach ($this->gedcomrecord->getFacts($facts) as $fact) {
			// Only display if it has a date or place (or both)
			if (($fact->getDate() || $fact->getPlace()) && $fact->canShow()) {
				switch ($style) {
					case 10:
					    return '<i>'.$fact->getLabel().' '. \MyArtJaub\Webtrees\Functions\FunctionsPrint::formatFactDateShort($fact) .'&nbsp;'. \MyArtJaub\Webtrees\Functions\FunctionsPrint::formatFactPlaceShort($fact, '%1') .'</i>';
					default:
						return $this->gedcomrecord->formatFirstMajorFact($facts, $style);
				}
			}
		}
		return '';
	}
}


?>