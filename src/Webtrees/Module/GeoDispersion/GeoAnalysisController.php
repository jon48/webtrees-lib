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
namespace MyArtJaub\Webtrees\Module\GeoDispersion;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Controller\BaseController;
use Fisharebest\Webtrees\Controller\PageController;
use Fisharebest\Webtrees\Filter;
use Fisharebest\Webtrees\Html;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Log;
use Fisharebest\Webtrees\Module;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Place;
use MyArtJaub\Webtrees\Constants;
use MyArtJaub\Webtrees\Controller\JsonController;
use MyArtJaub\Webtrees\Functions\Functions;
use MyArtJaub\Webtrees\Map\GoogleMapsProvider;
use MyArtJaub\Webtrees\Module\GeoDispersion\Model\GeoAnalysis;
use MyArtJaub\Webtrees\Module\GeoDispersion\Model\GeoAnalysisProvider;
use MyArtJaub\Webtrees\Module\Sosa\Model\SosaProvider;
use MyArtJaub\Webtrees\Mvc\Controller\MvcController;
use MyArtJaub\Webtrees\Mvc\View\ViewBag;
use MyArtJaub\Webtrees\Mvc\View\ViewFactory;

/**
 * Controller for GeoAnalysis
 */
class GeoAnalysisController extends MvcController
{
    /**
     * GeoAnalysis Provider
     * @var GeoAnalysisProvider $provider
     */
    protected $provider;
    
    /**
     * Constructor for GeoAnalysis controller
     * @param AbstractModule $module
     */
    public function __construct(AbstractModule $module) {
        parent::__construct($module);
        
        $this->provider = $this->module->getProvider();
    }    
    
    /**
     * Pages
     */
        
    /**
     * GeoAnalysis@index
     */
    public function index() {
        
        $controller = new PageController();
        $controller->setPageTitle(I18N::translate('Sosa Geographical dispersion'));
        
        $data = new ViewBag();
        $data->set('title', $controller->getPageTitle());
        $data->set('has_analysis', false);
        
        $ga_id = Filter::getInteger('ga_id');        
        
        if($ga_id && $ga = $this->provider->getGeoAnalysis($ga_id)) {
            $data->set('has_analysis', true);
            $data->set('geoanalysis', $ga);
            
            $controller
                ->addExternalJavascript(Constants::WT_RAPHAEL_JS_URL)
                ->addInlineJavascript('
                jQuery("#geodispersion-tabs").tabs();
                jQuery("#geodispersion-tabs").css("visibility", "visible");
                
                jQuery.get(
					"module.php",
					{
                        "mod" : "'. $this->module->getName() .'",  
                        "mod_action": "GeoAnalysis@dataTabs",
                        "ga_id" : "'.$ga_id.'"
                    },
					function(data){
						if(data){	    
							jQuery("#geodisp-data-general").html(data.generaltab);
							jQuery("#geodisp-data-generations").html(data.generationstab);
					    }
					    jQuery(".loading-image").hide();				    		
					},
					"json"
				);
            ');
        }
        
        ViewFactory::make('GeoAnalysis', $this, $controller, $data)->render();
    }
    
    /**
     * GeoAnalysis@listAll
     */
    public function listAll() {
        
        $controller = new PageController();
        $controller->setPageTitle(I18N::translate('Sosa Geographical dispersion'));
        
        $data = new ViewBag();
        $data->set('title', $controller->getPageTitle());
        $data->set('has_list', false);
        
        $ga_list = $this->provider->getGeoAnalysisList();
        if(count($ga_list) > 0 ) {
             $data->set('has_list', true);
             $data->set('geoanalysislist', $ga_list);
        }
        
        ViewFactory::make('GeoAnalysisList', $this, $controller, $data)->render();        
    }
    	
	/**
	 * GeoAnalysis@setStatus
	 */
    public function setStatus() {  
        global $WT_TREE;
        
        $controller = new JsonController();
        
        $ga_id = Filter::getInteger('ga_id');
        $ga = $this->provider->getGeoAnalysis($ga_id, false);
        
        $controller->restrictAccess(
            true // Filter::checkCsrf()   -- Cannot use CSRF on a GET request (modules can only work with GET requests)
            &&  Auth::isManager($WT_TREE) 
            && $ga
        );
        
        $status = Filter::getBool('status');
        $res = array('geoanalysis' => $ga->getId() , 'error' => null);
        try{
            $this->provider->setGeoAnalysisStatus($ga, $status);
            $res['status'] = $status;
			Log::addConfigurationLog('Module '.$this->module->getName().' : Geo Analysis ID "'.$ga->getId().'" has been '. ($status ? 'enabled' : 'diabled') .'.');
        }
        catch (\Exception $ex) {
            $res['error'] = $ex->getMessage();
			Log::addErrorLog('Module '.$this->module->getName().' : Geo Analysis ID "'.$ga->getId().'" could not be ' . ($status ? 'enabled' : 'diabled') .'. Error: '. $ex->getMessage());
        }
        
        $controller->pageHeader();
        if($res['error']) http_response_code(500);
        
        echo \Zend_Json::encode($res);
    }
    
	/**
     * GeoAnalysis@delete
     */
    public function delete() {
        global $WT_TREE;
    
        $controller = new JsonController();
    
        $ga_id = Filter::getInteger('ga_id');
        $ga = $this->provider->getGeoAnalysis($ga_id, false);
    
        $controller->restrictAccess(
            true // Filter::checkCsrf()   -- Cannot use CSRF on a GET request (modules can only work with GET requests)
            &&  Auth::isManager($WT_TREE)
            && $ga
            );
            
        $res = array('geoanalysis' => $ga->getId() , 'error' => null);
        try{
            $this->provider->deleteGeoAnalysis($ga);
			Log::addConfigurationLog('Module '.$this->module->getName().' : Geo Analysis ID "'.$ga->getId().'" has been deleted.');
        }
        catch (\Exception $ex) {
            $res['error'] = $ex->getMessage();
			Log::addErrorLog('Module '.$this->module->getName().' : Geo Analysis ID "'.$ga->getId().'" could not be deleted. Error: '. $ex->getMessage());
        }
    
        $controller->pageHeader();
        if($res['error']) http_response_code(500);
    
        echo \Zend_Json::encode($res);
    }
        	
    /**
     * GeoAnalysis@dataTabs
     */
    public function dataTabs() {
        global $WT_TREE;
        
        $controller = new JsonController();
        
        $ga_id = Filter::getInteger('ga_id');
        $ga = $this->provider->getGeoAnalysis($ga_id);
        $sosa_provider = new SosaProvider($WT_TREE, Auth::user());
        
        $controller
            ->restrictAccess($ga && $sosa_provider->isSetup())
            ->pageHeader();
        
        $jsonArray = array();
        
        list($placesDispGeneral, $placesDispGenerations) = $ga->getAnalysisResults($sosa_provider->getAllSosaWithGenerations());
        
        $flags = array();
        if($placesDispGeneral && $ga->getOptions() && $ga->getOptions()->isUsingFlags()) {
            $mapProvider = new GoogleMapsProvider();            
            foreach($placesDispGeneral['places'] as $place => $count) {
                $flags[$place] = $mapProvider->getPlaceIcon(new Place($place, $WT_TREE));
            }
        }
        
        $jsonArray['generaltab'] = $this->htmlPlacesAnalysisGeneralTab($ga, $placesDispGeneral, $flags);
        $jsonArray['generationstab'] = $this->htmlPlacesAnalysisGenerationsTab($ga, $placesDispGenerations, $flags);
        
        echo \Zend_Json::encode($jsonArray);
    }
    
	/**
	 * Returns HTML code for the GeoAnalysis general tab (can be either a map or a table).
	 *
	 * @param GeoAnalysis $ga Reference GeoAnalysis 
	 * @param array $placesGeneralResults Analysis results at a general level
	 * @param (null|array) $flags Array of flags
	 * @return string HTML code for the general tab
	 */
    protected function htmlPlacesAnalysisGeneralTab(GeoAnalysis $ga, $placesGeneralResults, $flags= null) {
        global $WT_TREE;
        
        $html = '';
        if($placesGeneralResults){
            $data = new ViewBag();
            
            $nb_found = $placesGeneralResults['knownsum'];
            $nb_other = 0;
            if(isset($placesGeneralResults['other'])) $nb_other =$placesGeneralResults['other'];
            $nb_unknown = $placesGeneralResults['unknown'];
            
            $data->set('stats_gen_nb_found', $nb_found);
            $data->set('stats_gen_nb_other', $nb_other);
            $data->set('stats_gen_nb_unknown', $nb_unknown);
            
            $data->set('use_flags', $ga->getOptions() && $ga->getOptions()->isUsingFlags());
            
            if($ga->hasMap()) {
                $max = $placesGeneralResults['max'];
                $map = $ga->getOptions()->getMap();
                $maxcolor = $map->getCanvas()->max_color;
                $hovercolor = $map->getCanvas()->hover_color;
                $results_by_subdivs = $map->getSubdivisions();
                $places_mappings = $map->getPlacesMappings();
                foreach ($placesGeneralResults['places'] as $location => $count) {
                    $levelvalues = array_reverse(array_map('trim',explode(',', $location)));
                    $level_map = $ga->getAnalysisLevel() - $ga->getOptions()->getMapLevel();
                    if($level_map >= 0 && $level_map < count($levelvalues)) {
                        $levelref = $levelvalues[0] . '@' . $levelvalues[$level_map];
                        if(!isset($results_by_subdivs[$levelref])) { $levelref = $levelvalues[0]; }
                    }
                    else {
                        $levelref = $levelvalues[0];
                    }
                    if(isset($places_mappings[$levelref])) $levelref = $places_mappings[$levelref];
                    if(isset($results_by_subdivs[$levelref])) {
                        $count_subd = isset($results_by_subdivs[$levelref]['count']) ? $results_by_subdivs[$levelref]['count'] : 0;
                        $count_subd  += $count;
                        $results_by_subdivs[$levelref]['count'] = $count_subd;   
                        $results_by_subdivs[$levelref]['transparency'] = Functions::safeDivision($count_subd, $max);
                        if($ga->getOptions()->isUsingFlags() && $flags) {
                            $results_by_subdivs[$levelref]['place'] = new Place($location, $WT_TREE);
                            $results_by_subdivs[$levelref]['flag'] = $flags[$location];
                        }
                    }
                }             
                
                $data->set('map', $ga->getOptions()->getMap());
                $data->set('results_by_subdivisions', $results_by_subdivs);
                
                $html = ViewFactory::make('GeoAnalysisTabGeneralMap', $this, new BaseController(), $data)->getHtmlPartial();
            }
            else {
                $results = $placesGeneralResults['places'];
                arsort($results);
                $data->set('results', $results);
                $data->set('analysis_level', $ga->getAnalysisLevel());
                
                $html = ViewFactory::make('GeoAnalysisTabGeneralTable', $this, new BaseController(), $data)->getHtmlPartial();
            }
        }
        else {
            $html = '<p class="warning">' . I18N::translate('No data is available for the general analysis.') . '</p>';
        }
        return $html;
    }
    
	/**
	 * Returns HTML code for the GeoAnalysis generations tab.
	 *
	 * @param GeoAnalysis $ga Reference GeoAnalysis 
	 * @param array $placesGenerationsResults Analysis results at a generations level
	 * @param (null|array) $flags Array of flags
	 * @return string HTML code for the generations tab
	 */
    protected function htmlPlacesAnalysisGenerationsTab(GeoAnalysis $ga, $placesGenerationsResults, $flags = null) {
        global $WT_TREE;
        
        $html = '<p class="warning">'.I18N::translate('No data is available for the generations analysis.').'<p>';
        if($placesGenerationsResults && $ga->getOptions()){
            $data = new ViewBag();
            
            ksort($placesGenerationsResults);
            
            $detailslevel = $ga->getOptions()->getMaxDetailsInGen();
            $data->set('max_details_gen', $detailslevel);    
            $data->set('use_flags', $ga->getOptions()->isUsingFlags());
            $data->set('analysis_level', $ga->getAnalysisLevel());
            $display_all_places = !is_null($detailslevel) && $detailslevel == 0;
            $data->set('display_all_places', $display_all_places);
            
            $results_by_gen = array();
            foreach($placesGenerationsResults as $gen => $genData){
                $sum = 0;
                $other = 0;
                $unknown = 0;
                if(isset($genData['sum'])) $sum = $genData['sum'];
                if(isset($genData['other'])) $other = $genData['other'];
                if(isset($genData['unknown'])) $unknown = $genData['unknown'];
                
                if($sum > 0) {                
                    $results_by_gen[$gen]['sum'] = $sum;
                    $results_by_gen[$gen]['other'] = $other;
                    $results_by_gen[$gen]['unknown'] = $unknown;
                    $results_by_gen[$gen]['places'] = array();                    
                    arsort($genData['places']);
                    
                    if($display_all_places){
                        foreach($genData['places'] as $placename=> $count){
                            $results_by_gen[$gen]['places'][$placename]['count'] = $count;
                            $levels = array_map('trim',explode(',', $placename));
                            
                            if($ga->getOptions() && $ga->getOptions()->isUsingFlags() && ($flag = $flags[$placename]) != ''){
                                $results_by_gen[$gen]['places'][$placename]['place'] = new Place($placename, $WT_TREE);
                                $results_by_gen[$gen]['places'][$placename]['flag'] = $flag;
                            }
                        }
                    }
                    else {
                        $tmp = $genData['places'];
                        if($other > 0) {
                            $tmp = array_slice($tmp, 0, 5, true);
                            $tmp['other'] = $other;
                            arsort($tmp);  
                        }                      
                        $results_by_gen[$gen]['places'] = array_slice($tmp, 0, 5, true);                        
                    }
                }
            }
            
            $data->set('results_by_generations', $results_by_gen);
            
            $html = ViewFactory::make('GeoAnalysisTabGenerations', $this, new BaseController(), $data)->getHtmlPartial();
            
        }
        else {
            $html = '<p class="warning">' . I18N::translate('No data is available for the generations analysis.') . '</p>';
        }
        return $html;
    }
        
}