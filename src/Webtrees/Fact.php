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

use Fisharebest\Webtrees\Date;
/**
 * Decorator class to extend native webtrees Fact class.
 * 
 * @see \Fisharebest\Webtrees\Fact
 */
class Fact {
		
	/**
	 * Maximum value for the IsSourced indicator.
	 * This value needs to match the maximum one used in the \MyArtJaub\Webtrees\Fact::isSourced method
	 * @var int
	 */
	const MAX_IS_SOURCED_LEVEL = 3;
	
	/**
	 * Maximum timespan between the date of a source and the date of the event to consider the source precise
	 * @var unknown DATE_PRECISION_MARGIN
	 */
	const DATE_PRECISION_MARGIN = 180;
	
	/** @var \Fisharebest\Webtrees\Fact Underlying base Fact */
	protected $fact; 
	
	/**
	* Contructor for the decorator
	*
	* @param \Fisharebest\Webtrees\Fact $fact_in The Fact to extend
	*/
	public function __construct(\Fisharebest\Webtrees\Fact $fact_in){
		$this->fact = $fact_in;
	}
	
	/**
	* Check if a fact has a date and is sourced
	* Values:
	* 		- 0, if no date is found for the fact
	* 		- -1, if the date is not precise
	* 		- -2, if the date is precise, but no source is found
	* 		- 1, if the date is precise, and a source is found
	* 		- 2, if the date is precise, a source exists, and is supported by a certificate (requires _ACT usage)
	* 		- 3, if the date is precise, a source exists, and the certificate supporting the fact is within an acceptable range of date
	*
	* @return int Level of sources
	*/
	public function isSourced(){
		$isSourced=0;
		$date = $this->fact->getDate();
		if($date->isOK()) {
			$isSourced=-1;
			if($date->qual1=='' && $date->minimumJulianDay() == $date->maximumJulianDay()){
				$isSourced=-2;
				$citations = $this->fact->getCitations();
				foreach($citations as $citation){
					$isSourced=max($isSourced, 1);
					if(preg_match('/3 _ACT (.*)/', $citation) ){
 						$isSourced=max($isSourced, 2);
 						preg_match_all("/4 DATE (.*)/", $citation, $datessource, PREG_SET_ORDER);
 						foreach($datessource as $daterec){
 							$datesource = new Date($daterec[1]);
 							if(abs($datesource->julianDay() - $date->julianDay()) < self::DATE_PRECISION_MARGIN){
 								$isSourced = max($isSourced, 3); //If this level increases, do not forget to change the constant MAX_IS_SOURCED_LEVEL
 							}
 						}
 					}
				}
			}
		}
		return $isSourced;
	}
	
}

?>