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

use \Fisharebest\Webtrees as fw;
use Fisharebest\Webtrees\Tree;

/**
 * Decorator class to extend native webtrees Individual class.
 * 
 * @see \Fisharebest\Webtrees\Family
 */
class Family extends GedcomRecord {

	/** @var bool|null Indicates whether the marriage event is sourced */
	protected $is_marriage_sourced = null;
	
	/**
	 * Extend \Fisharebest\Webtrees\Family getInstance, in order to retrieve directly a \MyArtJaub\Webtrees\Family object 
	 *
	 * @param string $xref
	 * @param Tree $tree
	 * @param string $gedcom
	 * @return NULL|\MyArtJaub\Webtrees\Family
	 */
	public static function getIntance($xref, Tree $tree, $gedcom = null){
		$dfam = null;
		$fam = fw\Family::getInstance($xref, $tree, $gedcom);
		if($fam){
			$dfam = new Family($fam);
		}
		return $dfam;
	}
	
	/**
	 * Find the spouse of a person, using the Xref comparison.
	 *
	 * @param Individual $person
	 *
	 * @return Individual|null
	 */
	public function getSpouseById(\Fisharebest\Webtrees\Individual $person) {
		if ($this->gedcomrecord->getWife() && 
				$person->getXref() === $this->gedcomrecord->getWife()->getXref()) {
			return $this->gedcomrecord->getHusband();
		} else {
			return $this->gedcomrecord->getWife();
		}
	}
		
}

?>