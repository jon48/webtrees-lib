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

/**
 * Decorator class to extend native webtrees Individual class.
 * 
 * @see \Fisharebest\Webtrees\Family
 * @todo snake_case
 */
class Family extends GedcomRecord {

	/** @var bool|null Indicates whether the marriage event is sourced */
	protected $_ismarriagesourced = null;
	
	/**
	 * Extend \Fisharebest\Webtrees\Family getInstance, in order to retrieve directly a \MyArtJaub\Webtrees\Family object 
	 *
	 * @param unknown_type $data Data to identify the individual
	 * @return \MyArtJaub\Webtrees\Family|null \MyArtJaub\Webtrees\Family instance
	 */
	public static function getIntance($data){
		$dfam = null;
		$fam = fw\Family::getInstance($data);
		if($fam){
			$dfam = new Family($fam);
		}
		return $dfam;
	}
	
	/**
	* Check if this family's marriages are sourced
	*
	* @return int Level of sources
	* */
	function isMarriageSourced(){
		if($this->_ismarriagesourced != null) return $this->_ismarriagesourced;
		$this->_ismarriagesourced = $this->isFactSourced(WT_EVENTS_MARR.'|MARC');
		return $this->_ismarriagesourced;
	}
		
}

?>