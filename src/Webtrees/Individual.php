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

use Fisharebest\Webtrees\Module;
use MyArtJaub\Webtrees\Module\Sosa\Model\SosaProvider;

/**
 * Decorator class to extend native webtrees Individual class.
 * 
 * @see \Fisharebest\Webtrees\Individual
 * @todo snake_case
 */
class Individual extends GedcomRecord {

	/** @var array|null List of titles the individal holds */	
	protected $_titles=null;
	
	/** @var string|null Individual's primary surname, without any privacy applied to it */
	protected $_unprotectedPrimarySurname = null;
	
	/** @var array|null List of Sosa numbers linked to this individual (based on the tree root individual)  */	
	protected $_sosa = null;
	
	/** @var bool|null Indicates whether the birth event is sourced */
	protected $_isbirthsourced = null;
	
	/** @var bool|null Indicates whether the death event is sourced */
	protected $_isdeathsourced = null;
	
	/**
	 * Extend \Fisharebest\Webtrees\Individual getInstance, in order to retrieve directly a  object 
	 *
	 * @param mixed $data Data to identify the individual
	 * @return Individual|null \MyArtJaub\Webtrees\Individual instance
	 */
	public static function getIntance($data){
		$indi = \Fisharebest\Webtrees\Individual::getInstance($data);
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
		if(is_null($this->_titles) && $module = Module::getModuleByName(Constants::MODULE_MAJ_MISC_NAME)){
			$pattern = '/(.*) (('.$module->getSetting('PG_TITLE_PREFIX', '').')(.*))/';
			$this->_titles=array();
			$titlefacts = $this->gedcomrecord->getFacts('TITL');
			foreach($titlefacts as $titlefact){
				$ct2 = preg_match_all($pattern, $titlefact->getValue(), $match2);
				if($ct2>0){
					$this->_titles[$match2[1][0]][]= trim($match2[2][0]);
				}
				else{
					$this->_titles[$titlefact->getValue()][]="";
				}
			}
		}
		return $this->_titles;
	}

	/**
	 * Returns primary Surname of the individual.
	 * Warning : no check of privacy if done in this function.
	 *
	 * @return string Primary surname
	 */
	public function getUnprotectedPrimarySurname() {
		if(!$this->_unprotectedPrimarySurname){
			$tmp=$this->gedcomrecord->getAllNames();
			$this->_unprotectedPrimarySurname = $tmp[$this->gedcomrecord->getPrimaryName()]['surname'];
		}
		return $this->_unprotectedPrimarySurname;
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
	 * Return whether an individual is a Sosa or not
	 *
	 * @return boolean Is the individual a Sosa ancestor
	 */
	public function isSosa(){
	    return count($this->getSosaNumbers()) > 0;
	}
	
	/**
	 * Get the list of Sosa numbers for this individual
	 * This list is cached.
	 *
	 * @uses \MyArtJaub\Webtrees\Functions\ModuleManager
	 * @return array List of Sosa numbers
	 */
	public function getSosaNumbers(){
	    if($this->_sosa === null) {
	        $provider = new SosaProvider($this->gedcomrecord->getTree());
	        $this->_sosa = $provider->getSosaNumbers($this->gedcomrecord);	        
	    }
	    return $this->_sosa;
	}
		
	/** 
	 * Check if this individual's birth is sourced
	 *
	 * @return int Level of sources
	 * */
	public function isBirthSourced(){
		if($this->_isbirthsourced != null) return $this->_isbirthsourced;
		$this->_isbirthsourced = $this->isFactSourced(WT_EVENTS_BIRT);
		return $this->_isbirthsourced;
	}
	
	/**
	* Check if this individual's death is sourced
	*
	* @return int Level of sources
	* */
	public function isDeathSourced(){
		if($this->_isdeathsourced != null) return $this->_isdeathsourced;
		$this->_isdeathsourced = $this->isFactSourced(WT_EVENTS_DEAT);
		return $this->_isdeathsourced;
	}
	
}

?>