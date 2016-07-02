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

use Fisharebest\Webtrees\Database;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\GedcomRecord;
use MyArtJaub\Webtrees\Constants;
use Fisharebest\Webtrees\Log;

/**
 * Provide geodispersion analysis data access
 */
class GeoAnalysisProvider {
    
    /**
     * Reference tree
     * @var Tree $tree
     */
    protected $tree;
    
    /**
     * Cached hierarchy of places in the Gedcom file.
     * 
     * @var (array|null) $place_hierarchy
     */
    protected $place_hierarchy;
    
    /**
     * Constructor for GeoAnalysis Provider.
     * A provider is defined in relation to a specific tree.
     *
     * @param Tree $tree
     */
    public function __construct(Tree $tree) {
        $this->tree = $tree;
        $this->place_hierarchy = null;
    }
    
    /**
     * Creates and returns a GeoAnalysis object from a data row.
     * The row data is expected to be an array with the indexes:
     *  - majgd_id: geodispersion analysis ID
     *  - majgd_descr: geodispersion analysis description/title
     *  - majgd_sublevel: Analysis level
     *  - majgd_useflagsgen: Use flags in places display
     *  - majgd_detailsgen: Number of top places
     *  - majgd_map: file name of the map
     *  - majgd_toplevel: parent level for the map
     * 
     * @param array $row
     * @return GeoAnalysis
     */
    protected function loadGeoAnalysisFromRow($row) {
        $options = new GeoDisplayOptions();
        $options
        ->setUsingFlags($row['majgd_useflagsgen'] == 'yes')
        ->setMaxDetailsInGen($row['majgd_detailsgen']);
        
        if($row['majgd_map']) {
            $options
            ->setMap(new OutlineMap($row['majgd_map']))
            ->setMapLevel($row['majgd_toplevel']);
        }
        
        $enabled = true;
        if(isset($row['majgd_status']) && $row['majgd_status'] == 'disabled') {
            $enabled = false;
        }
        
        return new GeoAnalysis(
            $this->tree,
            $row['majgd_id'],
            $row['majgd_descr'],
            $row['majgd_sublevel'],
            $options,
            $enabled
            );
    }
    
    /**
     * Returns the number of geographical analysis (active and inactive). 
     * 
     * @return int
     */
    public function getGeoAnalysisCount() {
        return Database::prepare(
            'SELECT COUNT(majgd_id)' .
            ' FROM `##maj_geodispersion`' .
            ' WHERE majgd_file = :gedcom_id'
            )->execute(array(
                'gedcom_id' => $this->tree->getTreeId()
            ))->fetchOne();
    }
    
    /**
     * Get a geographical analysis by its ID.
     * The function can only search for only enabled analysis, or all.
     * 
     * @param int $id geodispersion analysis ID
     * @param bool $only_enabled Search for only enabled geodispersion analysis
     * @return \MyArtJaub\Webtrees\Module\GeoDispersion\Model\GeoAnalysis|NULL
     */
    public function getGeoAnalysis($id, $only_enabled = true) {
        $args = array (
            'gedcom_id' => $this->tree->getTreeId(),
            'ga_id' => $id
        );
        
        $sql = 'SELECT majgd_id, majgd_descr, majgd_sublevel, majgd_map, majgd_toplevel, majgd_useflagsgen, majgd_detailsgen, majgd_status' .
            ' FROM `##maj_geodispersion`' .
            ' WHERE majgd_file = :gedcom_id AND majgd_id=:ga_id';
        if($only_enabled) {
            $sql .= ' AND majgd_status = :status';
            $args['status'] = 'enabled';
        }
        $sql .= ' ORDER BY majgd_descr';
        
        $ga_array = Database::prepare($sql)->execute($args)->fetchOneRow(\PDO::FETCH_ASSOC);
        
        if($ga_array) {
            return $this->loadGeoAnalysisFromRow($ga_array);
        }
        
        return null;            
    }
    
    /**
     * Add a new geodispersion analysis in the database, in a transactional manner.
     * When successful, eturns the newly created GeoAnalysis object.
     * 
     * @param string $description geodispersion analysis title
     * @param int $analysis_level Analysis level
     * @param string $map_file Filename of the map
     * @param int $map_top_level Parent level of the map
     * @param bool $use_flags Use flag in the place display
     * @param int $gen_details Number of top places to display
     * @return GeoAnalysis
     */
	public function createGeoAnalysis($description, $analysis_level, $map_file, $map_top_level, $use_flags, $gen_details) {
		try{
			Database::beginTransaction();
		
			Database::prepare(
				'INSERT INTO `##maj_geodispersion`'.
				' (majgd_file, majgd_descr, majgd_sublevel, majgd_map, majgd_toplevel, majgd_useflagsgen, majgd_detailsgen)'.
				' VALUES (:gedcom_id, :description, :analysis_level, :map, :map_top_level, :use_flags, :gen_details)'
			)->execute(array(
				'gedcom_id' => $this->tree->getTreeId(),
				'description' => $description,
				'analysis_level' => $analysis_level,
				'use_flags' => $use_flags ? 'yes' : 'no',
				'gen_details' => $gen_details,
				'map' => $map_file,
				'map_top_level' => $map_top_level        
			));
			
			$id = Database::lastInsertId();			
			$ga = $this->getGeoAnalysis($id, false);
			
			Database::commit();
		}
		catch(\Exception $ex) {
			Database::rollback();
			$ga = null;
			Log::addErrorLog('A new Geo Analysis failed to be created. Transaction rollbacked. Parameters ['.$description.', '.$analysis_level.','.$map_file.','.$map_top_level.','.$use_flags.', '.$gen_details.']. Exception: '.$ex->getMessage());
		}
		return $ga;
    }
	
    /**
     * Update a geodispersion analysis in the database, in transactional manner.
     * When successful, returns the updated GeoAnalysis object
     *  
     * @param GeoAnalysis $ga
     * @return GeoAnalysis
     */
    public function updateGeoAnalysis(GeoAnalysis $ga) {
        try {
			Database::beginTransaction();
		
			Database::prepare(
				'UPDATE `##maj_geodispersion`'.
				' SET majgd_descr = :description,'.
				' majgd_sublevel = :analysis_level,'.
				' majgd_map = :map,'.
				' majgd_toplevel = :map_top_level,'.
				' majgd_useflagsgen = :use_flags,'.
				' majgd_detailsgen = :gen_details'.
				' WHERE majgd_file = :gedcom_id AND majgd_id = :ga_id'
			)->execute(array(
				'gedcom_id' => $this->tree->getTreeId(),
				'ga_id' => $ga->getId(),
				'description' => $ga->getTitle(),
				'analysis_level' => $ga->getAnalysisLevel(),
				'use_flags' => $ga->getOptions() && $ga->getOptions()->isUsingFlags() ? 'yes' : 'no',
				'gen_details' => $ga->getOptions() ? $ga->getOptions()->getMaxDetailsInGen() : 0,
				'map' => $ga->hasMap() ? $ga->getOptions()->getMap()->getFileName() : null,
				'map_top_level' => $ga->hasMap() ? $ga->getOptions()->getMapLevel() : -100        
			));
			
			$ga = $this->getGeoAnalysis($ga->getId(), false);
			
			Database::commit();
		}
		catch(\Exception $ex) {		    
			Database::rollback();
			Log::addErrorLog('The Geo Analysis ID “' . $ga->getId() . '” failed to be updated. Transaction rollbacked. Exception: '.$ex->getMessage());
			$ga = null;
		}
		return $ga;
    }
    
    /**
     * Set the status of a specific analysis.
     * The status can be enabled (true), or disabled (false).
     * 
     * @param GeoAnalysis $ga
     * @param bool $status
     */
    public function setGeoAnalysisStatus(GeoAnalysis $ga, $status) {
        Database::prepare(
            'UPDATE `##maj_geodispersion`'.
            ' SET majgd_status = :status'.
            ' WHERE majgd_file = :gedcom_id AND majgd_id=:ga_id'
        )->execute(array(
                'gedcom_id' => $this->tree->getTreeId(),
                'status' => $status ? 'enabled' : 'disabled',
                'ga_id' => $ga->getId()
        ));
    }
    
    /**
     * Delete a geodispersion analysis from the database.
     * 
     * @param GeoAnalysis $ga
     */
    public function deleteGeoAnalysis(GeoAnalysis $ga) {
        Database::prepare(
            'DELETE FROM `##maj_geodispersion`'.
            ' WHERE majgd_file = :gedcom_id AND majgd_id=:ga_id'
            )->execute(array(
                'gedcom_id' => $this->tree->getTreeId(),
                'ga_id' => $ga->getId()
            ));
    }
        
    /**
     * Return the list of geodispersion analysis recorded and enabled for a specific GEDCOM
     *
     * @return array List of enabled maps
     */
    public function getGeoAnalysisList(){
        $res = array();
        
        $list = Database::prepare(
            'SELECT majgd_id, majgd_descr, majgd_sublevel, majgd_map, majgd_toplevel, majgd_useflagsgen, majgd_detailsgen' .
            ' FROM `##maj_geodispersion`' .
            ' WHERE majgd_file = :gedcom_id AND majgd_status = :status'.
            ' ORDER BY majgd_descr'
        )->execute(array(
            'gedcom_id' => $this->tree->getTreeId(),
            'status' => 'enabled'
        ))->fetchAll(\PDO::FETCH_ASSOC);
        
        foreach($list as $ga) {
           $res[] = $this->loadGeoAnalysisFromRow($ga);
        }
        
        return $res;
    }
    
    /**
     * Return the list of geodispersion analysis matching specified criterias.
     * 
     * @param string $search Search criteria in analysis description
     * @param array $order_by Columns to order by
     * @param int $start Offset to start with (for pagination)
     * @param int|null $limit Max number of items to return (for pagination)
     * @return \MyArtJaub\Webtrees\Module\GeoDispersion\Model\GeoAnalysis[]
     */
    public function getFilteredGeoAnalysisList($search = null, $order_by = null, $start = 0, $limit = null){
        $res = array();
            
        $sql = 
            'SELECT majgd_id, majgd_descr, majgd_sublevel, majgd_map, majgd_toplevel, majgd_useflagsgen, majgd_detailsgen, majgd_status' .
            ' FROM `##maj_geodispersion`' .
            ' WHERE majgd_file = :gedcom_id';
        
        $args = array('gedcom_id'=> $this->tree->getTreeId());
        
        if($search) {
            $sql .= ' AND majgd_descr LIKE CONCAT(\'%\', :search, \'%\')';
            $args['search'] = $search;
        }
        
        if ($order_by) {
            $sql .= ' ORDER BY ';
            foreach ($order_by as $key => $value) {
                if ($key > 0) {
                    $sql .= ',';
                }
                
                switch ($value['dir']) {
                    case 'asc':
                        $sql .= $value['column'] . ' ASC ';
                        break;
                    case 'desc':
                        $sql .= $value['column'] . ' DESC ';
                        break;
                }
            }
        } else {
            $sql = " ORDER BY majgd_descr ASC";
        }
        
        if ($limit) {
            $sql .= " LIMIT :limit OFFSET :offset";
            $args['limit']  = $limit;
            $args['offset'] = $start;
        }
            
        $data = Database::prepare($sql)->execute($args)->fetchAll(\PDO::FETCH_ASSOC);

        foreach($data as $ga) {
            $res[] = $this->loadGeoAnalysisFromRow($ga);
        }
        
        return $res;
    }
            
    /**
     * Returns the infered place hierarchy, determined from the Gedcom data.
     * Depending on the data, it can be based on the Gedcom Header description, or from a place example.
     * This is returned as an associative array:
     *      - type:    describe the source of the data (<em>header<em> / <em>data</em>)
     *      - hierarchy: an array of the place hierarchy (in reverse order of the gedcom)
     *      
     * @return array
     */
    public function getPlacesHierarchy() {
        if(!$this->place_hierarchy) {
            if($place_structure = $this->getPlacesHierarchyFromHeader()) {
                $this->place_hierarchy = array('type' => 'header', 'hierarchy' => $place_structure);
            }
            else {
                $this->place_hierarchy = array('type' => 'data', 'hierarchy' => $this->getPlacesHierarchyFromData());
            }            
        }
        return $this->place_hierarchy;        
    }
    
    /**
     * Returns an array of the place hierarchy, as defined in the GEDCOM header.
     * The places are reversed compared to normal GEDCOM structure.
     * 
     * @return array|null
     */
    protected function getPlacesHierarchyFromHeader() {
        $head = GedcomRecord::getInstance('HEAD', $this->tree);
        $head_place = $head->getFirstFact('PLAC');
        if($head_place && $head_place_value = $head_place->getAttribute('FORM')){
            return array_reverse(array_map('trim',explode(',', $head_place_value)));
        }
        return null;
    }
    
    /**
     * Returns an array of the place hierarchy, based on a random example of place within the GEDCOM.
     * It will look for the longest hierarchy in the tree.
     * The places are reversed compared to normal GEDCOM structure.
     * 
     * @return array
     */
    protected function getPlacesHierarchyFromData() {
        $random_place = null;
        $nb_levels = 0;
        
        //Select all '2 PLAC ' tags in the file and create array
        $places_list=array();
        $ged_data = Database::prepare(
            'SELECT i_gedcom AS gedcom'.
            ' FROM `##individuals`'.
            ' WHERE i_gedcom LIKE :gedcom AND i_file = :gedcom_id'.
            ' UNION ALL'.
            'SELECT f_gedcom AS gedcom'.
            ' FROM `##families`'.
            ' WHERE f_gedcom LIKE :gedcom AND f_file = :gedcom_id'
        )->execute(array(
            'gedcom' => '%\n2 PLAC %',
            'gedcom_id' => $this->tree->getTreeId()            
        ))->fetchOneColumn();
        foreach ($ged_data as $ged_datum) {
            preg_match_all('/\n2 PLAC (.+)/', $ged_datum, $matches);
            foreach ($matches[1] as $match) {
                $places_list[$match]=true;
            }
        }
        
        // Unique list of places
        $places_list=array_keys($places_list);
        
        //sort the array, limit to unique values, and count them
        $places_parts=array();
        usort($places_list, array('I18N', 'strcasecmp'));
        $nb_places = count($places_list);
        
        //calculate maximum no. of levels to display
        $has_found_good_example = false;
        foreach($places_list as $place){
            $levels = explode(",", $place);
            $parts = count($levels);
            if ($parts >= $nb_levels){
                $nb_levels = $parts;
                if(!$has_found_good_example){
                    $random_place = $$place;
                    if(min(array_map('strlen', $levels)) > 0){
                        $has_found_good_example = true;
                    }
                }
            }
        }
        
        return array_reverse(array_map('trim',explode(',', $randomPlace)));
    }
    
    /**
     * Returns the list of geodispersion maps available within the maps folder.
     * 
     * @return \MyArtJaub\Webtrees\Module\GeoDispersion\Model\OutlineMap[]
     */
    public function getOutlineMapsList() {
        $res = array();
        $root_path = WT_ROOT.WT_MODULES_DIR.Constants::MODULE_MAJ_GEODISP_NAME.'/maps/';
        if(is_dir($root_path)){
            $dir = opendir($root_path);
            while (($file=readdir($dir))!== false) {
                if (preg_match('/^[a-zA-Z0-9_]+.xml$/', $file)) {
                    $res[base64_encode($file)] = new OutlineMap($file, true);
                }
            }
        }
        return $res;
    }
}
 