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

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Database;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\User;
use MyArtJaub\Webtrees\Functions\Functions;

/**
 * Provide Sosa data access
 */
class SosaProvider {
    
    /**
     * Maximum number of generation the database is able to hold.
     * @var int MAX_DB_GENERATIONS
     */
    const MAX_DB_GENERATIONS = 64;
    
    /**
     * System's default user (ID -1 in the database
     * @var User $default_user
     */
    protected static $default_user;
    
    /**
     * Reference user
     * @var User $user
     */
    protected $user;
    
    /**
     * Reference tree
     * @var Tree $tree
     */
    protected $tree;
    
    /**
     * Cached list of Sosa Individuals by generation
     * Format: key = generation, value = array ( sosa => Individual ID)
     * @var array $sosa_list_by_gen
     */
    protected $sosa_list_by_gen;
    
    /**
     * Cached list of Sosa Families by generation
     * Format: key = generation, value = array ( sosa => Family ID)
     * @var unknown $sosa_fam_list_by_gen
     */
    protected $sosa_fam_list_by_gen;
    
    /**
     * Cached array of statistics by generation
     * Format:  key = generation, 
     *          value = array(
     *              sosaCount, sosaTotalCount, diffSosaTotalCount, firstBirth, lastBirth, avgBirth
     *           )
     * @var array $statistics_tab
     */
    protected $statistics_tab;
    
    /**
     * Has the provider's initialisation completed
     * @var bool $is_setup
     */
    protected $is_setup;
    
    /**
     * Constructor for Sosa Provider.
     * A provider is defined in relation to a specific tree and reference user.
     * 
     * @param Tree $tree
     * @param User $user
     */
    public function __construct(Tree $tree, User $user = null) {
        if(self::$default_user === null) 
            self::$default_user = User::find(-1);
        
        $this->tree = $tree;
        $this->user = $user;
        $this->is_setup = true;
        if($this->user === null) $this->user = Auth::user();
        if(strlen($this->user->getUserId()) == 0) $this->user = self::$default_user;
        
        // Check if the user, or the default user, has a root already setup;
        if(empty($this->getRootIndiId())) {
            if($this->user == self::$default_user) {  // If the default user is not setup
                $this->is_setup = false;
            }
            else {
                $this->user = self::$default_user;
                $this->is_setup = $this->getRootIndiId() === null;
            }            
        }
    }
    
    /**
     * Returns is the Provider has been successfully set up
     * @return bool
     */
    public function isSetup() {
        return $this->is_setup;
    }
    
    /**
     * Return the reference tree
     * 
     *  @return Tree Reference tree
     */
    public function getTree() {
        return $this->tree;
    }
    
    /**
     * Return the reference user
     * 
     * @return User
     */
    public function getUser() {
        return $this->user;
    }
    
    /**
     * Return the root individual ID for the reference tree and user.
     * @return string Individual ID
     */
    public function getRootIndiId() {
        return $this->tree->getUserPreference($this->user, 'MAJ_SOSA_ROOT_ID');
    }
    
    /**
     * Return the root individual for the reference tree and user.
     * @return Individual Individual
     */
    public function getRootIndi() {
        $root_indi_id = $this->getRootIndiId();
        if(!empty($root_indi_id)) {
            return Individual::getInstance($root_indi_id, $this->tree);
        }
        return null;
    }
       
    /*****************
     * DATA CRUD LAYER
     *****************/
    
    /**
     * Remove all Sosa entries related to the gedcom file and user
     */
    public function deleteAll() {
        if(!$this->is_setup) return;
        Database::prepare(
            'DELETE FROM `##maj_sosa`'.
            ' WHERE majs_gedcom_id= :tree_id and majs_user_id = :user_id ')
            ->execute(array(
                'tree_id' => $this->tree->getTreeId(), 
                'user_id' => $this->user->getUserId()                
            ));
    }
    
    /**
     * Remove all ancestors of a sosa number
     * 
     * @param int $sosa
     */
    public function deleteAncestors($sosa) {
        if(!$this->is_setup) return;
        $gen = Functions::getGeneration($sosa);
        Database::prepare(
            'DELETE FROM `##maj_sosa`'.
            ' WHERE majs_gedcom_id=:tree_id and majs_user_id = :user_id' .
            ' AND majs_gen >= :gen' .
            ' AND FLOOR(majs_sosa / (POW(2, (majs_gen - :gen)))) = :sosa'
        )->execute(array(
            'tree_id' => $this->tree->getTreeId(), 
            'user_id' => $this->user->getUserId(),
            'gen' => $gen,
            'sosa' => $sosa
        ));
    }    
    
    /**
     * Insert (or update if already existing) a list of Sosa individuals
     * @param array $sosa_records
     */
    public function insertOrUpdate($sosa_records) {
        if(!$this->is_setup) return;
        
        $treeid = $this->tree->getTreeId();
        $userid = $this->user->getUserId();
        $questionmarks_table = array();
        $values_table = array();
        
        $i = 0;
        foreach  ($sosa_records as $row) {
            $gen = Functions::getGeneration($row['sosa']);
            if($gen <= self::MAX_DB_GENERATIONS) {
                $questionmarks_table[] = 
                    '(:tree_id'.$i.', :user_id'.$i.', :sosa'.$i.', :indi_id'.$i.', :gen'.$i.', :byear'.$i.', :byearest'.$i.', :dyear'.$i.', :dyearest'.$i.')';
                $values_table = array_merge(
                    $values_table, 
                    array(
                        'tree_id'.$i => $treeid, 
                        'user_id'.$i => $userid, 
                        'sosa'.$i => $row['sosa'], 
                        'indi_id'.$i => $row['indi'], 
                        'gen'.$i => Functions::getGeneration($row['sosa']),
                        'byear'.$i => $row['birth_year'],
                        'byearest'.$i => $row['birth_year_est'],
                        'dyear'.$i => $row['death_year'],
                        'dyearest'.$i => $row['death_year_est']
                    )
                );
            }
            $i++;
        }
        
        $sql = 'REPLACE INTO `##maj_sosa`' .
            ' (majs_gedcom_id, majs_user_id, majs_sosa, majs_i_id, majs_gen, majs_birth_year, majs_birth_year_est, majs_death_year, majs_death_year_est)' .
            ' VALUES '. implode(',', $questionmarks_table);
        Database::prepare($sql)->execute($values_table);
    }
    
    /****************
     * SIMPLE QUERIES
     ****************/
    
    /**
     * Returns the list of Sosa numbers to which an individual is related.
     * Format: key = sosa number, value = generation for the Sosa number
     * 
     * @param Individual $indi
     * @return array Array of sosa numbers
     */
    public function getSosaNumbers(Individual $indi) {
        if(!$this->is_setup) return array();
        return Database::prepare(
                'SELECT majs_sosa, majs_gen FROM `##maj_sosa`'.
                ' WHERE majs_i_id=:indi_id AND majs_gedcom_id=:tree_id AND majs_user_id=:user_id'
            )->execute(array(
                'indi_id' => $indi->getXref(), 
                'tree_id' => $this->tree->getTreeId(), 
                'user_id' => $this->user->getUserId()
            ))->fetchAssoc();
    }
    
    /**
     * Get the last generation of Sosa ancestors
     *
     * @return number Last generation if found, 1 otherwise
     */
    public function getLastGeneration() {
        if(!$this->is_setup) return;
        return Database::prepare(
                'SELECT MAX(majs_gen) FROM `##maj_sosa`'.
                ' WHERE majs_gedcom_id=:tree_id AND majs_user_id=:user_id'
            )->execute(array(
                'tree_id' => $this->tree->getTreeId(), 
                'user_id' => $this->user->getUserId()                
            ))->fetchOne() ?: 1;
    }
    
    /*************
     * SOSA LISTS
     *************/
    
    /**
     * Return the list of all sosas, with the generations it belongs to
     *
     * @return array Associative array of Sosa ancestors, with their generation, comma separated
     */
    public function getAllSosaWithGenerations(){
        if(!$this->is_setup) return array();
        return Database::prepare(
            'SELECT majs_i_id AS indi,' .
            ' GROUP_CONCAT(DISTINCT majs_gen ORDER BY majs_gen ASC SEPARATOR ",") AS generations' .
            ' FROM `##maj_sosa`' .
            ' WHERE majs_gedcom_id=:tree_id AND majs_user_id=:user_id' .
            ' GROUP BY majs_i_id'
        )->execute(array(
            'tree_id' => $this->tree->getTreeId(),
            'user_id' => $this->user->getUserId()
        ))->fetchAssoc();
    }
    
    /**
     * Get an associative array of Sosa individuals in generation G. Keys are Sosa numbers, values individuals.
     *
     * @param number $gen Generation
     * @return array Array of Sosa individuals
     */
    public function getSosaListAtGeneration($gen){
        if(!$this->is_setup) return array();
        if(!$this->sosa_list_by_gen)
            $this->sosa_list_by_gen = array();
        
        if($gen){
            if(!isset($this->sosa_list_by_gen[$gen])){
                $this->sosa_list_by_gen[$gen] = Database::prepare(
                    'SELECT majs_sosa AS sosa, majs_i_id AS indi'.
                    ' FROM `##maj_sosa`'.
                    ' WHERE majs_gedcom_id=:tree_id AND majs_user_id=:user_id'.
                    ' AND majs_gen = :gen'.
                    ' ORDER BY majs_sosa ASC')
                ->execute(array(
                    'tree_id' => $this->tree->getTreeId(),
                    'user_id' => $this->user->getUserId(),
                    'gen' => $gen
                ))
                ->fetchAssoc();
            }
            return $this->sosa_list_by_gen[$gen];
        }
        return array();
    }
    
    /**
     * Get an associative array of Sosa families in generation G. Keys are Sosa numbers for the husband, values families.
     *
     * @param number $gen Generation
     * @return array Array of Sosa families
     */
    public function getFamilySosaListAtGeneration($gen){
        if(!$this->is_setup) return array();
        if(!$this->sosa_fam_list_by_gen)
            $this->sosa_fam_list_by_gen = array();
        
        if($gen){
            if(!isset($this->sosa_fam_list_by_gen[$gen])){
                $this->sosa_fam_list_by_gen[$gen] = Database::prepare(
                    'SELECT s1.majs_sosa AS sosa, f_id AS fam'.
                    ' FROM `##families`'.
                    ' INNER JOIN `##maj_sosa` AS s1 ON (`##families`.f_husb = s1.majs_i_id AND `##families`.f_file = s1.majs_gedcom_id)'.
                    ' INNER JOIN `##maj_sosa` AS s2 ON (`##families`.f_wife = s2.majs_i_id AND `##families`.f_file = s2.majs_gedcom_id)'.
                    ' WHERE s1.majs_sosa + 1 = s2.majs_sosa'.
                    ' AND s1.majs_gedcom_id= :tree_id AND s1.majs_user_id=:user_id'.
                    ' AND s2.majs_gedcom_id= :tree_id AND s2.majs_user_id=:user_id'.
                    ' AND s1.majs_gen = :gen'.
                    ' ORDER BY s1.majs_sosa ASC'
                    )
                    ->execute(array(
                        'tree_id' => $this->tree->getTreeId(),
                        'user_id' => $this->user->getUserId(),
                        'gen' => $gen
                    ))
                    ->fetchAssoc();
            }
            return $this->sosa_fam_list_by_gen[$gen];
        }
        return array();
    }
    
    /**
     * Get an associative array of Sosa individuals in generation G who are missing parents. Keys are Sosa numbers, values individuals.
     *
     * @param number $gen Generation
     * @return array Array of Sosa individuals
     */
    public function getMissingSosaListAtGeneration($gen){
        if(!$this->is_setup) return array();    
        if($gen){
            return $this->sosa_list_by_gen[$gen] = Database::prepare(
                'SELECT schild.majs_sosa sosa, schild.majs_i_id indi, sfat.majs_sosa IS NOT NULL has_father, smot.majs_sosa IS NOT NULL has_mother'.
                ' FROM `##maj_sosa` schild'.
                ' LEFT JOIN `##maj_sosa` sfat ON ((schild.majs_sosa * 2) = sfat.majs_sosa AND schild.majs_gedcom_id = sfat.majs_gedcom_id AND schild.majs_user_id = sfat.majs_user_id)'.
                ' LEFT JOIN `##maj_sosa` smot ON ((schild.majs_sosa * 2 + 1) = smot.majs_sosa AND schild.majs_gedcom_id = smot.majs_gedcom_id AND schild.majs_user_id = smot.majs_user_id)'.
                ' WHERE schild.majs_gedcom_id = :tree_id AND schild.majs_user_id = :user_id'.
                ' AND schild.majs_gen = :gen'.
                ' AND (sfat.majs_sosa IS NULL OR smot.majs_sosa IS NULL)'.
                ' ORDER BY schild.majs_sosa ASC')
                ->execute(array(
                    'tree_id' => $this->tree->getTreeId(),
                    'user_id' => $this->user->getUserId(),
                    'gen' => $gen - 1
                ))->fetchAll(\PDO::FETCH_ASSOC);
        }
        return array();
    }
    
    
    
    /*************
     * STATISTICS
     *************/
    /**
     * Get the statistic array detailed by generation.
     * Statistics for each generation are:
     * 	- The number of Sosa in generation
     * 	- The number of Sosa up to generation
     *  - The number of distinct Sosa up to generation
     *  - The year of the first birth in generation
     *  - The year of the first estimated birth in generation
     *  - The year of the last birth in generation
     *  - The year of the last estimated birth in generation
     *  - The average year of birth in generation
     *
     * @return array Statistics array
     */
    public function getStatisticsByGeneration() {
        if(!$this->is_setup) return array();
        if(!$this->statistics_tab) {
            $this->statistics_tab = array();
            if($maxGeneration = $this->getLastGeneration()) {
                for ($gen = 1; $gen <= $maxGeneration; $gen++) {
                    $birthStats = $this->getStatsBirthYearInGeneration($gen);
                    $this->statistics_tab[$gen] = array(
                        'sosaCount'				=>	$this->getSosaCountAtGeneration($gen),
                        'sosaTotalCount'		=>	$this->getSosaCountUpToGeneration($gen),
                        'diffSosaTotalCount'	=>	$this->getDifferentSosaCountUpToGeneration($gen),
                        'firstBirth'			=>	$birthStats['first'],
                        'firstEstimatedBirth'	=>	$birthStats['first_est'],
                        'lastBirth'				=>	$birthStats['last'],
                        'lastEstimatedBirth'	=>	$birthStats['last_est'],
                        'avgBirth'				=>	$birthStats['avg']
                    );
                }
            }
        }
        return $this->statistics_tab;        
    }
    
	/**
	 * How many individuals exist in the tree.
	 *
	 * @return int
	 */
	public function getTotalIndividuals() {
	    if(!$this->is_setup) return 0;
	    return Database::prepare(
	        'SELECT COUNT(*) FROM `##individuals`' .
	        ' WHERE i_file = :tree_id')
	        ->execute(array('tree_id' => $this->tree->getTreeId()))
	        ->fetchOne() ?: 0;
	}
    
    /**
     * Get the total Sosa count for all generations
     *
     * @return number Number of Sosas
     */
    public function getSosaCount(){
        if(!$this->is_setup) return 0;
        return Database::prepare(
            'SELECT COUNT(majs_sosa) FROM `##maj_sosa`' .
            ' WHERE majs_gedcom_id=:tree_id AND majs_user_id=:user_id')
            ->execute(array(
                'tree_id' => $this->tree->getTreeId(), 
                'user_id' => $this->user->getUserId() 
            ))->fetchOne() ?: 0;
    }
    
    /**
     * Get the number of Sosa in a specific generation.
     *
     * @param number $gen Generation
     * @return number Number of Sosas in generation
     */
    public function getSosaCountAtGeneration($gen){
        if(!$this->is_setup) return 0;
        return Database::prepare(
            'SELECT COUNT(majs_sosa) FROM `##maj_sosa`' .
            ' WHERE majs_gedcom_id=:tree_id AND majs_user_id=:user_id'.
            ' AND majs_gen= :gen')
        ->execute(array(
                'tree_id' => $this->tree->getTreeId(), 
                'user_id' => $this->user->getUserId(),
                'gen' => $gen            
        ))->fetchOne() ?: 0;
    }
    
    /**
     * Get the total number of Sosa up to a specific generation.
     *
     * @param number $gen Generation
     * @return number Total number of Sosas up to generation
     */
    public function getSosaCountUpToGeneration($gen){
        if(!$this->is_setup) return 0;
        return Database::prepare(
            'SELECT COUNT(majs_sosa) FROM `##maj_sosa`' .
            ' WHERE majs_gedcom_id=:tree_id AND majs_user_id=:user_id'.
            ' AND majs_gen <= :gen')
        ->execute(array(
                'tree_id' => $this->tree->getTreeId(), 
                'user_id' => $this->user->getUserId(),
                'gen' => $gen 
        ))->fetchOne() ?: 0;
    }
    
    /**
     * Get the total number of distinct Sosa individual for all generations.
     *
     * @return number Total number of distinct individual
     */
    public function getDifferentSosaCount(){
        if(!$this->is_setup) return 0;
        return Database::prepare(
            'SELECT COUNT(DISTINCT majs_i_id) FROM `##maj_sosa`' .
            ' WHERE majs_gedcom_id=:tree_id AND majs_user_id=:user_id')
        ->execute(array(
                'tree_id' => $this->tree->getTreeId(), 
                'user_id' => $this->user->getUserId()
        ))->fetchOne() ?: 0;
    }
    
    /**
     * Get the number of distinct Sosa individual up to a specific generation.
     *
     * @param number $gen Generation
     * @return number Number of distinct Sosa individuals up to generation
     */
    public function getDifferentSosaCountUpToGeneration($gen){
        if(!$this->is_setup) return 0;
        return Database::prepare(
            'SELECT COUNT(DISTINCT majs_i_id) FROM `##maj_sosa`' .
            ' WHERE majs_gedcom_id=:tree_id AND majs_user_id=:user_id'.
            ' AND majs_gen <= :gen')
        ->execute(array(
                'tree_id' => $this->tree->getTreeId(), 
                'user_id' => $this->user->getUserId(),
                'gen' => $gen 
        ))->fetchOne() ?: 0;
    }
    
    /**
     * Get an array of birth statistics for a specific generation
     * Statistics are :
     * 	- first : First birth year in generation
     *  - first_est: First estimated birth year in generation
     *  - last : Last birth year in generation
     *  - last_est : Last estimated birth year in generation
     *  - avg : Average birth year (based on non-estimated birth date)
     *
     * @param number $gen Generation
     * @return array Birth statistics array
     */
    public function getStatsBirthYearInGeneration($gen){
        if(!$this->is_setup) return array('first' => 0, 'first_est' => 0, 'avg' => 0, 'last' => 0, 'last_est' => 0);
        return Database::prepare(
            'SELECT'.
            ' MIN(majs_birth_year) AS first, MIN(majs_birth_year_est) AS first_est,'.
            ' AVG(majs_birth_year) AS avg,'.
            ' MAX(majs_birth_year) AS last, MAX(majs_birth_year_est) AS last_est'.
            ' FROM `##maj_sosa`'.
            ' WHERE majs_gedcom_id=:tree_id AND majs_user_id=:user_id'.
            ' AND majs_gen=:gen')
            ->execute(array(
                'tree_id' => $this->tree->getTreeId(), 
                'user_id' => $this->user->getUserId(),
                'gen' => $gen))
            ->fetchOneRow(\PDO::FETCH_ASSOC) ?: array('first' => 0, 'first_est' => 0, 'avg' => 0, 'last' => 0, 'last_est' => 0);
    }
    
    /**
     * Get the mean generation time, based on a linear regression of birth years and generations
     *
     * @return number|NULL Mean generation time
     */
    public function getMeanGenerationTime(){
        if(!$this->is_setup) return;
        if(!$this->statistics_tab){
            $this->getStatisticsByGeneration();
        }
        //Linear regression on x=generation and y=birthdate
        $sum_xy = 0;
        $sum_x=0;
        $sum_y=0;
        $sum_x2=0;
        $n=count($this->statistics_tab);
        foreach($this->statistics_tab as $gen=>$stats){
            $sum_xy+=$gen*$stats['avgBirth'];
            $sum_x+=$gen;
            $sum_y+=$stats['avgBirth'];
            $sum_x2+=$gen*$gen;
        }
        $denom=($n*$sum_x2)-($sum_x*$sum_x);
        if($denom!=0){
            return -(($n*$sum_xy)-($sum_x*$sum_y))/($denom);
        }
        return null;
    }
    
    /**
     * Return an array of the mean generation depth and standard deviation for all Sosa ancestors at a given generation.
     * Sosa 1 is of generation 1.
     * 
     * Mean generation depth and deviation are calculated based on the works of Marie-Héléne Cazes and Pierre Cazes,
     * published in Population (French Edition), Vol. 51, No. 1 (Jan. - Feb., 1996), pp. 117-140
     * http://kintip.net/index.php?option=com_jdownloads&task=download.send&id=9&catid=4&m=0
     * 
     * Format: 
     *  - key : sosa number of the ancestor
     *  - values: array
     *      - root_ancestor_id : ID of the ancestor
     *      - mean_gen_depth : Mean generation depth
     *      - stddev_gen_depth : Standard deviation of generation depth
     *  
     * @param number $gen Sosa generation
     * @return array
     */
    public function getGenerationDepthStatsAtGen($gen) {
        if(!$this->is_setup) return array();
        $gen_depth_stats_raw = Database::prepare(
            'SELECT stats_by_gen.root_ancestor AS root_ancestor_sosa,'.
            '   sosa_list.majs_i_id as root_ancestor_id,'.
            '   1 + SUM( (majs_gen_norm) * ( 2 * full_root_count + semi_root_count) /  (2 * POWER(2, majs_gen_norm))) AS mean_gen_depth,'.
            '   SQRT('. 
            '       SUM(POWER(majs_gen_norm, 2) * ( 2 * full_root_count + semi_root_count) /  (2 * POWER(2, majs_gen_norm)))'.
            '       - POWER( SUM( (majs_gen_norm) * ( 2 * full_root_count + semi_root_count) /  (2 * POWER(2, majs_gen_norm))), 2)'.
            '   ) AS stddev_gen_depth'.
            ' FROM('.
            '   SELECT'.
            '       sosa.majs_gedcom_id,'.
            '       sosa.majs_user_id,'.
            '       sosa.majs_gen - :gen AS majs_gen_norm,'.
            '       FLOOR(((sosa.majs_sosa / POW(2, sosa.majs_gen -1 )) - 1) * POWER(2, :gen - 1)) + POWER(2, :gen - 1) AS root_ancestor,'.
            '       SUM(case when sosa_fat.majs_i_id IS NULL AND sosa_mot.majs_i_id IS NULL THEN 1 ELSE 0 END) AS full_root_count,'.
            '       SUM(case when sosa_fat.majs_i_id IS NULL AND sosa_mot.majs_i_id IS NULL THEN 0 ELSE 1 END) As semi_root_count'.
            '   FROM `##maj_sosa` AS sosa'.
            '   LEFT JOIN `##maj_sosa` AS sosa_fat ON sosa_fat.majs_sosa = 2 * sosa.majs_sosa'.
            '       AND sosa_fat.majs_gedcom_id = sosa.majs_gedcom_id'.
            '       AND sosa_fat.majs_user_id = sosa.majs_user_id'.
            '   LEFT JOIN `##maj_sosa` AS sosa_mot ON sosa_mot.majs_sosa = 2 * sosa.majs_sosa + 1'.
            '       AND sosa_mot.majs_gedcom_id = sosa.majs_gedcom_id'.
            '       AND sosa_mot.majs_user_id = sosa.majs_user_id'.
            '   WHERE sosa.majs_gedcom_id = :tree_id'.
            '       AND sosa.majs_user_id = :user_id'.
            '       AND sosa.majs_gen >=  :gen'.
            '       AND (sosa_fat.majs_i_id IS NULL OR sosa_mot.majs_i_id IS NULL)'.
            '   GROUP BY sosa.majs_gen, root_ancestor'.
            ' ) AS stats_by_gen'.
            ' INNER JOIN `##maj_sosa` sosa_list ON sosa_list.majs_gedcom_id = stats_by_gen.majs_gedcom_id'.
            '   AND sosa_list.majs_user_id = stats_by_gen.majs_user_id'.
            '   AND sosa_list.majs_sosa = stats_by_gen.root_ancestor'.
            ' GROUP BY stats_by_gen.root_ancestor, sosa_list.majs_i_id'.
            ' ORDER BY stats_by_gen.root_ancestor')
        ->execute(array(
            'tree_id' => $this->tree->getTreeId(),
            'user_id' => $this->user->getUserId(),
            'gen' => $gen
        ))->fetchAll() ?: array();
        
        $gen_depth_stats = array();
        foreach ($gen_depth_stats_raw as $gen_depth_stat) {
            $gen_depth_stats[$gen_depth_stat->root_ancestor_sosa] = array(
                'root_ancestor_id' => $gen_depth_stat->root_ancestor_id,
                'mean_gen_depth' => $gen_depth_stat->mean_gen_depth,
                'stddev_gen_depth' => $gen_depth_stat->stddev_gen_depth
            );
        }
        return $gen_depth_stats;
    }
    
    /**
     * Return a computed array of statistics about the dispersion of ancestors across the ancestors
     * at a specified generation.
     * This statistics cannot be used for generations above 11, as it would cause a out of range in MySQL
     * 
     * Format: 
     *  - key : a base-2 representation of the ancestor at generation G for which exclusive ancestors have been found,
     *          -1 is used for shared ancestors
     *          For instance base2(0100) = base10(4) represent the maternal grand father
     *  - values: number of ancestors exclusively in the ancestors of the ancestor in key
     *  
     *  For instance a result at generation 3 could be :
     *      array (   -1        =>  12      -> 12 ancestors are shared by the grand-parents
     *                base10(1) =>  32      -> 32 ancestors are exclusive to the paternal grand-father
     *                base10(2) =>  25      -> 25 ancestors are exclusive to the paternal grand-mother
     *                base10(4) =>  12      -> 12 ancestors are exclusive to the maternal grand-father
     *                base10(8) =>  30      -> 30 ancestors are exclusive to the maternal grand-mother
     *            )
     *  
     * @param int $gen Reference generation
     * @return array
     */
    public function getAncestorDispersionForGen($gen) {
        if(!$this->is_setup || $gen > 11) return array();  // Going further than 11 gen will be out of range in the query
        return Database::prepare(
            'SELECT branches, count(i_id)'.
            ' FROM ('.
            '   SELECT i_id,'.
            '       CASE'.
            '           WHEN CEIL(LOG2(SUM(branch))) = LOG2(SUM(branch)) THEN SUM(branch)'.
            '           ELSE -1'.   // We put all ancestors shared between some branches in the same bucket
            '       END branches'.
            '   FROM ('.
            '       SELECT DISTINCT majs_i_id i_id,'.
            '           POW(2, FLOOR(majs_sosa / POW(2, (majs_gen - :gen))) - POW(2, :gen -1)) branch'.
            '       FROM `##maj_sosa`'.
            '       WHERE majs_gedcom_id = :tree_id AND majs_user_id = :user_id'.
            '           AND majs_gen >= :gen'.
            '   ) indistat'.
            '   GROUP BY i_id'.
            ') grouped'.
            ' GROUP BY branches')
            ->execute(array(
                'tree_id' => $this->tree->getTreeId(), 
                'user_id' => $this->user->getUserId(),
                'gen' => $gen
            ))->fetchAssoc() ?: array();
    }
    
    /**
     * Return an array of the most duplicated root Sosa ancestors.
     * The number of ancestors to return is limited by the parameter $limit.
     * If several individuals are tied when reaching the limit, none of them are returned,
     * which means that there can be less individuals returned than requested.
     * 
     * Format: 
     *  - key : root Sosa individual
     *  - value: number of duplications of the ancestor (e.g. 3 if it appears 3 times)
     * 
     * @param number $limit Maximum number of individuals to return
     * @return array 
     */
    public function getTopMultiSosaAncestorsNoTies($limit) {
        if(!$this->is_setup) return array();
        return Database::prepare(
            'SELECT sosa_i_id, sosa_count FROM ('.
            '   SELECT'.
            '       top_sosa.sosa_i_id, top_sosa.sosa_count, top_sosa.sosa_min,'.
            '       @keep := IF(@prev_count = 0 OR sosa_count = @prev_count, @keep, 1) AS keep,'.
            '       @prev_count := top_sosa.sosa_count AS prev_count'.
            '   FROM ('.
            '       SELECT'.
            '           sosa.majs_i_id sosa_i_id,'.
            '           COUNT(sosa.majs_sosa) sosa_count,'.
            '           MIN(sosa.majs_sosa) sosa_min'.
            '       FROM ##maj_sosa AS sosa'.
            '       LEFT JOIN ##maj_sosa AS sosa_fat ON sosa_fat.majs_sosa = 2 * sosa.majs_sosa'.   // Link to sosa's father
            '           AND sosa.majs_gedcom_id = sosa_fat.majs_gedcom_id'.
            '           AND sosa.majs_user_id = sosa_fat.majs_user_id'.
            '       LEFT JOIN ##maj_sosa AS sosa_mot on sosa_mot.majs_sosa = (2 * sosa.majs_sosa + 1)'.  // Link to sosa's mother
            '           AND sosa.majs_gedcom_id = sosa_fat.majs_gedcom_id'.
            '           AND sosa.majs_user_id = sosa_fat.majs_user_id'.
            '       WHERE sosa.majs_gedcom_id = :tree_id'.
            '       AND sosa.majs_user_id = :user_id'.
            '       AND sosa_fat.majs_sosa IS NULL'.    // We keep only root individuals, i.e. those with no father or mother
            '       AND sosa_mot.majs_sosa IS NULL'. 
            '       GROUP BY sosa.majs_i_id'.
            '       HAVING COUNT(sosa.majs_sosa) > 1'.   // Limit to the duplicate sosas.
            '       ORDER BY COUNT(sosa.majs_sosa) DESC'.
            '       LIMIT ' . ($limit + 1) . // We want to select one more than required
            '   ) AS top_sosa,'.
            '   (SELECT @prev_count := 0, @keep := 0) x'.
            '   ORDER BY top_sosa.sosa_count ASC'.
            ' ) top_sosa_list'.
            ' WHERE keep = 1'.
            ' ORDER BY sosa_count DESC, sosa_min ASC'
            )->execute(array(
                'tree_id' => $this->tree->getTreeId(),
                'user_id' => $this->user->getUserId()
            ))->fetchAssoc() ?: array();
    }
    
               
}
 