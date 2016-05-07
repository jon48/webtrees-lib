<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage GeoDispersion
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module\GeoDispersion\Model;

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Place;
use Fisharebest\Webtrees\Tree;
use MyArtJaub\Webtrees\Constants;
use MyArtJaub\Webtrees\Individual;

/**
 * Provide Sosa data access
 */
class GeoAnalysis {
    
	/**
	 * Geo Analysis ID
	 * @var int $id
	 */
    protected $id;
    
	/**
	 * Geo Analysis Title
	 * @var string $title
	 */
    protected $title;
    
	/**
	 * Level of the Gedcom hierarchy for the analysis
	 * @var int $analysis_level
	 */
    protected $analysis_level;
    
    /**
     * Display options
     * @var GeoDisplayOptions $options
     */
    protected $options;
    
    /**
     * Reference tree
     * @var Tree $tree
     */
    protected $tree;
    
    /**
     * Is the analysis enabled
     * @var bool $enabled
     */
    protected $enabled;
    
    /**
     * Constructor for GeoAnalysis.
     *
     * @param Tree $tree Reference tree
	 * @param int $id GeoAnalysis ID
	 * @param string $title GeoAnalysis title
	 * @param int $analysis_level Analysis level
	 * @param (GeoDisplayOptions|null) $options Display options
	 * @param bool $enabled Is analysis enabled
     */
    public function __construct(Tree $tree, $id, $title, $analysis_level, GeoDisplayOptions $options = null, $enabled = true) {
        $this->tree = $tree;
        $this->id = $id;
        $this->title = $title;
        $this->analysis_level = $analysis_level;
        $this->options = $options;
        $this->enabled = $enabled;
    }
    
	/**
	 * Get the analysis title
	 * @return string
	 */
    public function getTitle() {
        return $this->title;
    }
    
	/**
	 * Set the analysis title
	 *
	 * @param string $title
	 * @return self Enable method-chaining
	 */
    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }
    
	/**
	 * Get the analysis ID
	 * @return int
	 */
    public function getId() {
        return $this->id;
    }
    
	/**
	 * Get the analysis status (enabled/disabled)
	 * @return bool
	 */
    public function isEnabled() {
        return $this->enabled;
    }
    
    /**
     * Get analysis options
     * @return \MyArtJaub\Webtrees\Module\GeoDispersion\Model\GeoDisplayOptions
     */
    public function getOptions() {
        return $this->options;
    }
    
	/**
     * Set analysis options
	 *
     * @param \MyArtJaub\Webtrees\Module\GeoDispersion\Model\GeoDisplayOptions $options
	 * @return self Enable method-chaining
     */
    public function setOptions(GeoDisplayOptions $options) {
        $this->options = $options;
        return $this;
    }
    
	/**
	 * Get analysis level
	 * @return int
	 */
    public function getAnalysisLevel() {
        return $this->analysis_level;
    }
    
	/**
	 * Get analysis level
	 *
	 * @param int $analysis_level
	 * @return self Enable method-chaining
	 */
    public function setAnalysisLevel($analysis_level) {
        $this->analysis_level = $analysis_level;
        return $this;
    }
    
	/**
	 * Check whether the analysis has a linked map
	 *
	 * @return bool
	 */
    public function hasMap() {
        return $this->options && $this->options->getMap();
    }
    
	/**
	 * Get the URL for the GeoAnalysis.
	 *
	 * @return string
	 */
	 public function getHtmlUrl() {
        return 'module.php?mod='. Constants::MODULE_MAJ_GEODISP_NAME . '&mod_action=GeoAnalysis&ga_id=' . $this->getId() . '&ged=' . $this->tree->getNameUrl();
    }
    
    /**
     * Return the dispersion analysis tables.
     * Two arrays are returned :
     * 	- the General analysis, which returns the number of ancestors for each place found, plus 4 additional indicators :
     * 		- knownsum : Number of known places
     * 		- unknown : Number of unknown places
     * 		- max : Maximum count of ancestors within a place
     * 		- other : Other places (not in the top level area)
     * - the Generations analysis, which returns the number of ancestors for each place found for each generation, plus 3 additional indicators within each generation :
     * 		- sum : Number of known places
     * 		- unknown : Number of unknown places
     * 		- other : Other places (not in the top level area)
     *
     * @param array $sosalist List of all sosas
     * @return array Array of the general and generations table
     */
    public function getAnalysisResults($sosalist) {
        $placesDispGeneral = null;
        $placesDispGenerations = null;
        
        if($sosalist && count($sosalist) > 0) {
            $placesDispGeneral['knownsum'] = 0;
            $placesDispGeneral['unknown'] = 0;
            $placesDispGeneral['max'] = 0;
            $placesDispGeneral['places'] = array();
            foreach($sosalist as $sosaid => $gens) {
                $sosa = Individual::getIntance($sosaid, $this->tree);
                $place =$sosa->getSignificantPlace();
                $genstab = explode(',', $gens);
                $isUnknown=true;
                if($sosa->getDerivedRecord()->canShow() && !is_null($place)){
                    $levels = array_reverse(array_map('trim',explode(',', $place)));
                    if(count($levels)>= $this->analysis_level){                        
                        $toplevelvalues = array();
                        if($this->hasMap() && $toplevelvalue = $this->options->getMap()->getTopLevelName()) {
                            $toplevelvalues = array_map('trim',explode(',', strtolower($toplevelvalue)));
                        }
                        if(!$this->hasMap() 
                            || is_null($this->options->getMapLevel()) 
                            || $this->options->getMap()->getTopLevelName() == '*' 
                            || (
                                $this->options->getMapLevel() <= $this->analysis_level 
                                && $this->options->getMapLevel() > 0
                                && count($levels) >= $this->options->getMapLevel()
                                && in_array(strtolower($levels[$this->options->getMapLevel()-1]), $toplevelvalues)
                            )
                        ) {
                            $placest = implode(I18N::$list_separator, array_slice($levels, 0, $this->analysis_level));
                            if(isset($placesDispGeneral['places'][$placest])) {
                                $placesDispGeneral['places'][$placest] += 1;
                            }
                            else { 
                                $placesDispGeneral['places'][$placest] = 1;
                            }
                            if($placesDispGeneral['places'][$placest]>$placesDispGeneral['max'])
                                $placesDispGeneral['max'] = $placesDispGeneral['places'][$placest];
                            foreach($genstab as $gen) {
                                if(isset($placesDispGenerations[$gen]['places'][$placest])) {
                                    $placesDispGenerations[$gen]['places'][$placest] += 1;
                                }
                                else { 
                                    $placesDispGenerations[$gen]['places'][$placest] = 1;
                                }
                                if(isset($placesDispGenerations[$gen]['sum'])) {
                                    $placesDispGenerations[$gen]['sum'] += 1;
                                }
                                else { 
                                    $placesDispGenerations[$gen]['sum'] = 1;
                                }
                            }
                        }
                        else{
                            if(isset($placesDispGeneral['other'])) {
                                $placesDispGeneral['other'] += 1;
                            }
                            else { 
                                $placesDispGeneral['other'] = 1;
                            }
                            foreach($genstab as $gen) {
                                if(isset($placesDispGenerations[$gen]['other'])) {
                                    $placesDispGenerations[$gen]['other'] += 1;
                                }
                                else { 
                                    $placesDispGenerations[$gen]['other'] = 1;
                                }
                            }
                        }
                        $placesDispGeneral['knownsum'] += 1;
                        $isUnknown = false;
                    }
                }
                if($isUnknown){
                    $placesDispGeneral['unknown'] += 1;
                    foreach($genstab as $gen) {
                        if(isset($placesDispGenerations[$gen]['unknown'])) { 
                            $placesDispGenerations[$gen]['unknown'] += 1;
                        }
                        else { 
                            $placesDispGenerations[$gen]['unknown'] = 1; 
                        }
                    }
                }
            }
           
        }        
        
        return array($placesDispGeneral, $placesDispGenerations);        
    }
    
                   
}
 