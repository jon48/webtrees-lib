<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2010-2015, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

namespace MyArtJaub\Webtrees;

use \Fisharebest\Webtrees as fw;
use \MyArtJaub\Webtrees as mw;

use fw\Auth;

/**
 * Decorator class to extend native webtrees GedcomRecord class.
 * 
 * @see \Fisharebest\Webtrees\GedcomRecord
 */
class GedcomRecord {

	/** @var \Fisharebest\Webtrees\GedcomRecord Underlying base GedcomRecord */
	protected $gedcomrecord;

	/** @var bool Is the GedcomRecord sourced (cache) */ 
	protected $_issourced=null;

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
			&& $this->gedcomrecord->privatizeGedcom(fw\Auth::PRIV_HIDE) === null;
	}
	

	/**
	 * Deprecated in favour of the formatFirstMajorFact functions, for naming convention reasons.
	 * 
	 * @see MyArtJaub\Webtrees\GedcomRecord::formatFirstMajorFact
	 * @deprecated
	 * 
	 * @param string $facts List of facts to find information from
	 * @param int $style Style to apply to the information. Number >= 10 should be used in this function, lower number will return the core function.

	 */
	public function format_first_major_fact($facts, $style) {
		return $this->formatFirstMajorFact($facts, $style);
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
						return '<i>'.$fact->getLabel().' '. mw\Functions\FunctionsPrint::formatFactDateShort($fact) .'&nbsp;'. mw\Functions\FunctionsPrint::formatFactPlaceShort($fact, '%1') .'</i>';
						break;
					default:
						return $this->gedcomrecord->formatFirstMajorFact($facts, $style);
				}
			}
		}
		return '';
	}

	/**
	 * Check if the IsSourced information can be displayed
	 *
	 * @param int $access_level
	 * @return boolean
	 */
	public function canDisplayIsSourced($access_level=WT_USER_ACCESS_LEVEL){
		global $global_facts;

		if(!$this->gedcomrecord->canShow($access_level)) return false;

		if (isset($global_facts['SOUR'])) {
			return $global_facts['SOUR']>=$access_level;
		}

		return true;
	}

	/**
	 * Check if a gedcom record is sourced
	 * Values:
	 * 		- -1, if the record has no sources attached
	 * 		- 1, if the record has a source attached
	 * 		- 2, if the record has a source, and a certificate supporting it
	 *
	 * @return int Level of sources
	 */
	public function isSourced(){
		if($this->_issourced != null) return $this->_issourced;
		$this->_issourced=-1;
		$sourcesfacts = $this->gedcomrecord->getFacts('SOUR');
		foreach($sourcesfacts as $sourcefact){
			$this->_issourced=max($this->_issourced, 1);
			if($sourcefact->getAttribute('_ACT')){
				$this->_issourced=max($this->_issourced, 2);
			}
		}
		return $this->_issourced;
	}

	/**
	 * Check is an event associated to this record is sourced
	 *
	 * @param string $eventslist
	 * @return int Level of sources
	 */
	public function isFactSourced($eventslist){
		$isSourced=0;
		$facts = $this->gedcomrecord->getFacts($eventslist);
		foreach($facts as $fact){
			if($isSourced < Fact::MAX_IS_SOURCED_LEVEL){
				$dfact = new Fact($fact);
				$tmpIsSourced = $dfact->isSourced();
				if($tmpIsSourced != 0) {
					if($isSourced==0) {
						$isSourced =  $tmpIsSourced;
					}
					else{
						$isSourced = max($isSourced, $tmpIsSourced);
					}
				}
			}
		}
		return $isSourced;
	}


}


?>