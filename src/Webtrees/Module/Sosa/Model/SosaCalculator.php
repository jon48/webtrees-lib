<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Sosa
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module\Sosa\Model;

use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\User;
use Fisharebest\Webtrees\Individual;

/**
 * Perform computations of Sosa
 */
class SosaCalculator {
    
    /**
     * Maximium size for the temporary Sosa table
     * @var int TMP_SOSA_TABLE_LIMIT
     */
    const TMP_SOSA_TABLE_LIMIT = 1000;
    
    /**
     * Reference user
     * @var Fisharebest\Webtrees\User $user
     */
    protected $user;
    
    /**
     * Reference tree
     * @var Fisharebest\Webtrees\Tree $tree
     */
    protected $tree;
    
    /**
     * Sosa Provider for the calculator
     * @var \MyArtJaub\Webtrees\Module\Sosa\Model\SosaCalculator $sosa_provider
     */
    protected $sosa_provider;
    
    /**
     * Temporary Sosa table, used during construction
     * @var array $tmp_sosa_table
     */
    protected $tmp_sosa_table;
    
    /**
     * Constructor for the Sosa Calculator
     * @param Tree $tree
     * @param User $user
     */
    public function __construct(Tree $tree, User $user) {        
        $this->tree = $tree;
        $this->user = $user;
        
        $this->sosa_provider = new SosaProvider($this->tree, $this->user);;
    }
    
    /**
     * Compute all Sosa ancestors from the user's root individual.
     * @return bool Result of the computation
     */
    public function computeAll() {
        $root_id = $this->tree->getUserPreference($this->user, 'MAJ_SOSA_ROOT_ID');        
        $indi = Individual::getInstance($root_id, $this->tree);
        if($indi){
            $this->sosa_provider->deleteAll();
            $this->addNode($indi, 1);
            $this->flushTmpSosaTable(true);
            return true;
        }
        return false;
    }
    
    /**
     * Compute all Sosa Ancestors from a specified Individual
     * @param Individual $indi
     * @return bool Result of the computation
     */
    public function computeFromIndividual(Individual $indi) {
        $dindi = new \MyArtJaub\Webtrees\Individual($indi);
        $current_sosas = $dindi->getSosaNumbers();
        foreach($current_sosas as $current_sosa => $gen) {
            $this->sosa_provider->deleteAncestors($current_sosa);
            $this->addNode($indi, $current_sosa);
        }
        $this->flushTmpSosaTable(true);
        return true;
    }
    
    /**
     * Recursive method to add individual to the Sosa table, and flush it regularly
     * @param Individual $indi Individual to add
     * @param int $sosa Individual's sosa
     */
    protected function addNode(Individual $indi, $sosa) {                
        $birth_year = $indi->getBirthDate()->gregorianYear();
        $birth_year_est = $birth_year === 0 ? $indi->getEstimatedBirthDate()->gregorianYear() : $birth_year;
        
        $death_year = $indi->getDeathDate()->gregorianYear();
        $death_year_est = $death_year === 0 ? $indi->getEstimatedDeathDate()->gregorianYear() : $death_year;
        
        $this->tmp_sosa_table[] = array(
            'indi' => $indi->getXref(),
            'sosa' => $sosa,
            'birth_year' => $birth_year === 0 ? null : $birth_year,
            'birth_year_est' => $birth_year_est === 0 ? null : $birth_year_est,
            'death_year' => $death_year === 0 ? null : $death_year,
            'death_year_est' => $death_year_est === 0 ? null : $death_year_est
        );
        
        $this->flushTmpSosaTable();
        
        if($fam = $indi->getPrimaryChildFamily()) {
            if($husb = $fam->getHusband()) $this->addNode($husb, 2 * $sosa);
            if($wife = $fam->getWife()) $this->addNode($wife, 2 * $sosa + 1);
        }
    }
    
    /**
     * Write sosas in the table, if the number of items is superior to the limit, or if forced.
     *
     * @param bool $force Should the flush be forced
     */
    protected function flushTmpSosaTable($force = false) {
        if( count($this->tmp_sosa_table)> 0 && 
            ($force ||  count($this->tmp_sosa_table) >= self::TMP_SOSA_TABLE_LIMIT)){            
                $this->sosa_provider->insertOrUpdate($this->tmp_sosa_table);
                $this->tmp_sosa_table = array();
        }
    }
               
}
 