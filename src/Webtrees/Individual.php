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

use Fisharebest\Webtrees\Module;
use Fisharebest\Webtrees\Tree;
use MyArtJaub\Webtrees\Module\Sosa\Model\SosaProvider;

/**
 * Decorator class to extend native webtrees Individual class.
 * 
 * @see \Fisharebest\Webtrees\Individual
 */
class Individual extends GedcomRecord {

	/** @var array|null List of titles the individal holds */	
	protected $titles=null;
	
	/** @var string|null Individual's primary surname, without any privacy applied to it */
	protected $unprotected_prim_surname = null;
	
		
	/**
	 * Extend \Fisharebest\Webtrees\Individual getInstance, in order to retrieve directly a  object 
	 * 
	 * @param string $xref
	 * @param Tree $tree
	 * @param null|string $gedcom
	 * @return null|Individual
	 */
	public static function getIntance($xref, Tree $tree, $gedcom = null){
		$indi = \Fisharebest\Webtrees\Individual::getInstance($xref, $tree, $gedcom);
		if($indi){
			return new Individual($indi);
		}
		return null;
	}

	/**
	 * Get an array of the different titles (tag TITL) of an individual
	 * 
	 * @return array Array of titles
	 */
	public function getTitles(){
		if(is_null($this->titles) && $module = Module::getModuleByName(Constants::MODULE_MAJ_MISC_NAME)){
			$pattern = '/(.*) (('.$module->getSetting('MAJ_TITLE_PREFIX', '').')(.*))/';
			$this->titles=array();
			$titlefacts = $this->gedcomrecord->getFacts('TITL');
			foreach($titlefacts as $titlefact){
				$ct2 = preg_match_all($pattern, $titlefact->getValue(), $match2);
				if($ct2>0){
					$this->titles[$match2[1][0]][]= trim($match2[2][0]);
				}
				else{
					$this->titles[$titlefact->getValue()][]='';
				}
			}
		}
		return $this->titles;
	}

	/**
	 * Returns primary Surname of the individual.
	 * Warning : no check of privacy if done in this function.
	 *
	 * @return string Primary surname
	 */
	public function getUnprotectedPrimarySurname() {
		if(!$this->unprotected_prim_surname){
			$tmp=$this->gedcomrecord->getAllNames();
			$this->unprotected_prim_surname = $tmp[$this->gedcomrecord->getPrimaryName()]['surname'];
		}
		return $this->unprotected_prim_surname;
	}
	
	/**
	 * Returns an estimated birth place based on statistics on the base
	 *
	 * @param boolean $perc Should the coefficient of reliability be returned
	 * @return string|array Estimated birth place if found, null otherwise
	 */
	public function getEstimatedBirthPlace($perc=false){
		if($bplace = $this->gedcomrecord->getBirthPlace()){
			if($perc){
				return array ($bplace, 1);
			}
			else{
				return $bplace;
			}
		}
		return null;
	}
	
	/**
	 * Returns a significant place for the individual
	 *
	 * @param boolean $perc Should the coefficient of reliability be returned
	 * @return string|array Estimated birth place if found, null otherwise
	 */
	public function getSignificantPlace(){
	    if($bplace = $this->gedcomrecord->getBirthPlace()){
	        return $bplace;
	    }
	
	    foreach ($this->gedcomrecord->getAllEventPlaces('RESI') as $rplace) {
	        if ($rplace) {
	            return $rplace;
	        }
	    }
	
	    if($dplace = $this->gedcomrecord->getDeathPlace()){
	        return $dplace;
	    }
	
	    foreach($this->gedcomrecord->getSpouseFamilies() as $fams) {
	        foreach ($fams->getAllEventPlaces('RESI') as $rplace) {
	            if ($rplace) {
	                return $rplace;
	            }
	        }
	    }
	
	    return null;
	}
}

?>