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
use Fisharebest\Webtrees\Controller\PageController;
use Fisharebest\Webtrees\Filter;
use Fisharebest\Webtrees\FlashMessages;
use Fisharebest\Webtrees\Html;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Log;
use Fisharebest\Webtrees\Module;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Theme;
use Fisharebest\Webtrees\Theme\AdministrationTheme;
use Fisharebest\Webtrees\Tree;
use MyArtJaub\Webtrees\Controller\JsonController;
use MyArtJaub\Webtrees\Module\GeoDispersion\Model\GeoAnalysis;
use MyArtJaub\Webtrees\Module\GeoDispersion\Model\GeoAnalysisProvider;
use MyArtJaub\Webtrees\Module\GeoDispersion\Model\OutlineMap;
use MyArtJaub\Webtrees\Mvc\Controller\MvcController;
use MyArtJaub\Webtrees\Mvc\View\ViewBag;
use MyArtJaub\Webtrees\Mvc\View\ViewFactory;
use Rhumsaa\Uuid\Uuid;

/**
 * Controller for GeoDispersion AdminConfig
 */
class AdminConfigController extends MvcController
{    
    /**
     * GeoAnalysis Provider
     * @var GeoAnalysisProvider $provider
     */
    protected $provider;    
    
    /**
     * Constructor for Admin Config controller
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
     * AdminConfig@index
     */
    public function index() {
        global $WT_TREE;
        
        Theme::theme(new AdministrationTheme)->init($WT_TREE);
        $controller = new PageController();
        $controller
            ->restrictAccess(Auth::isManager($WT_TREE))
            ->setPageTitle($this->module->getTitle());
        
        $data = new ViewBag();
        $data->set('title', $controller->getPageTitle());
        $data->set('tree', $WT_TREE);
        
        $data->set('root_url', 'module.php?mod=' . $this->module->getName() . '&mod_action=AdminConfig');
                
        $table_id = 'table-geoanalysis-' . Uuid::uuid4();
        $data->set('table_id', $table_id);
        
        $other_trees = array();
        foreach (Tree::getAll() as $tree) {
            if($tree->getTreeId() != $WT_TREE->getTreeId()) $other_trees[] = $tree;
        }      
        $data->set('other_trees', $other_trees);
        
        $data->set('places_hierarchy', $this->provider->getPlacesHierarchy());
        
        $controller
            ->addExternalJavascript(WT_JQUERY_DATATABLES_JS_URL)
            ->addExternalJavascript(WT_DATATABLES_BOOTSTRAP_JS_URL)
            ->addInlineJavascript('
                //Datatable initialisation
				jQuery.fn.dataTableExt.oSort["unicode-asc"  ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
				jQuery.fn.dataTableExt.oSort["unicode-desc" ]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
				jQuery.fn.dataTableExt.oSort["num-html-asc" ]=function(a,b) {a=parseFloat(a.replace(/<[^<]*>/, "")); b=parseFloat(b.replace(/<[^<]*>/, "")); return (a<b) ? -1 : (a>b ? 1 : 0);};
				jQuery.fn.dataTableExt.oSort["num-html-desc"]=function(a,b) {a=parseFloat(a.replace(/<[^<]*>/, "")); b=parseFloat(b.replace(/<[^<]*>/, "")); return (a>b) ? -1 : (a<b ? 1 : 0);};
	
				var geoAnalysisTable = jQuery("#'.$table_id.'")
                .on("draw.dt", function ( e, settings, json, xhr ) {
                    jQuery("[data-toggle=\'tooltip\']").tooltip();
                }).DataTable({
					'.I18N::datatablesI18N().',			
					sorting: [[3, "asc"], [4, "asc"]],
					pageLength: 10,
                    processing: true,
                    serverSide : true,
					ajax : {
						url : "module.php?mod='.$this->module->getName().'&mod_action=AdminConfig@jsonGeoAnalysisList&ged='. $WT_TREE->getNameUrl().'",
                        type : "POST"
					},
                    columns: [
						/* 0 Edit		 	*/ { sortable: false, className: "text-center"},
                        /* 1 ID             */ { visible: false },
						/* 2 Enabled 		*/ { sortable: false, className: "text-center"  },
						/* 3 Description	*/ null,
						/* 4 Analysis Level	*/ { dataSort: 5, className: "text-center" },
						/* 5 ANAL_LEVEL_SORT*/ { visible: false },
						/* 6 Map 	        */ { sortable: false, className: "text-center" },
						/* 7 Map Top Level 	*/ { sortable: false, className: "text-center" },
						/* 8 Use Flags     	*/ { sortable: false, className: "text-center" },					
						/* 9 Place Details	*/ { sortable: false, className: "text-center" }
					],
				});
                
                ')
                ->addInlineJavascript('				
                    function set_geoanalysis_status(ga_id, status, gedcom) {
                		jQuery.ajax({
                            url: "module.php", 
                            type: "GET",
                            data: {
                			    mod: "' . $this->module->getName() .'",
                                mod_action:  "GeoAnalysis@setStatus",
                			    ga_id: ga_id,
                			    ged: typeof gedcom === "undefined" ? WT_GEDCOM : gedcom,
                                status: status
                            },
                            error: function(result, stat, error) {
                                var err = typeof result.responseJSON === "undefined" ? error : result.responseJSON.error;
                                alert("' . I18N::translate('An error occured while editing this analysis:') . '" + err);
                            },
                            complete: function(result, stat) {
                                geoAnalysisTable.ajax.reload(null, false);
                            }                            
                		});
                    }
                    
                    function delete_geoanalysis(ga_id, status, gedcom) {
                		jQuery.ajax({
                            url: "module.php", 
                            type: "GET",
                            data: {
                			    mod: "' . $this->module->getName() .'",
                                mod_action:  "GeoAnalysis@delete",
                			    ga_id: ga_id,
                			    ged: typeof gedcom === "undefined" ? WT_GEDCOM : gedcom
                            },
                            error: function(result, stat, error) {
                                var err = typeof result.responseJSON === "undefined" ? error : result.responseJSON.error;
                                alert("' . I18N::translate('An error occured while deleting this analysis:') . '" + err);
                            },
                            complete: function(result, stat) {
                                geoAnalysisTable.ajax.reload(null, false);
                            }                            
                		});
                    }
                ');
        
        
        ViewFactory::make('AdminConfig', $this, $controller, $data)->render();
    }

    /**
     * AdminConfig@jsonGeoAnalysisList
     */
    public function jsonGeoAnalysisList() {
        global $WT_TREE;
        
        $controller = new JsonController();
        $controller
            ->restrictAccess(Auth::isManager($WT_TREE));
        
        // Generate an AJAX/JSON response for datatables to load a block of rows
        $search = Filter::postArray('search');
        if($search) $search = $search['value'];
        $start  = Filter::postInteger('start');
        $length = Filter::postInteger('length');
        $order  = Filter::postArray('order');
        
        foreach($order as $key => &$value) {
            switch($value['column']) {
                case 3:
                    $value['column'] = 'majgd_descr';
                    break;
                case 5;
                    $value['column'] = 'majgd_sublevel';
                    break;
                default:
                    unset($order[$key]);
            }
        }
        
        /** @var GeoAnalysisProvider $provider */
        $provider = $this->module->getProvider();
        
        $list = $provider->getFilteredGeoAnalysisList($search, $order, $start, $length);
        $recordsFiltered = count($list);
        $recordsTotal = $this->provider->getGeoAnalysisCount();
        
        $data = array();
        $place_hierarchy = $this->provider->getPlacesHierarchy();
        foreach($list as $ga) {
            /** @var GeoAnalysis $ga */
            
            $datum = array();
            $options= $ga->getOptions();
            
            $datum[0] = '
                <div class="btn-group">
                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-pencil"></i><span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                       <li>
                            <a href="#" onclick="return set_geoanalysis_status('. $ga->getId().', '.($ga->isEnabled() ? 'false' : 'true').', \''.Filter::escapeJs($WT_TREE->getName()).'\');">
                                <i class="fa fa-fw '.($ga->isEnabled() ? 'fa-times' : 'fa-check').'"></i> ' . ($ga->isEnabled() ? I18N::translate('Disable') : I18N::translate('Enable')) . '
                            </a>
                       </li>
                        <li>
                            <a href="module.php?mod='.$this->module->getName().'&mod_action=AdminConfig@edit&ga_id='.$ga->getId().'&ged='.$WT_TREE->getName().'">
                                <i class="fa fa-fw fa-pencil"></i> ' . I18N::translate('Edit') . '
                            </a>
                       </li>
                       <li class="divider" />
                       <li>
                            <a href="#" onclick="return delete_geoanalysis('. $ga->getId().', \''.Filter::escapeJs($WT_TREE->getName()).'\');">
                                <i class="fa fa-fw fa-trash-o"></i> ' . I18N::translate('Delete') . '
                            </a>
                       </li>
                    </ul>
                </div>';
		    $datum[1] = $ga->getId();
		    $datum[2] = $ga->isEnabled() ? 
				'<i class="fa fa-check"></i><span class="sr-only">'.I18N::translate('Enabled').'</span>' : 
				'<i class="fa fa-times"></i><span class="sr-only">'.I18N::translate('Disabled').'</span>';
		    $datum[3] = $ga->getTitle();
		    $analysis_level = $ga->getAnalysisLevel();
		    if($place_hierarchy['type'] == 'header') {
		        $datum[4] = $place_hierarchy['hierarchy'][$analysis_level - 1];
		    } else {
		        $datum[4] = $analysis_level . '(' . $place_hierarchy['hierarchy'][$analysis_level - 1] . ')';
		    }
		    $datum[5] = $ga->getAnalysisLevel();
		    $datum[6] = '<i class="fa fa-times"></i><span class="sr-only">'.I18N::translate('None').'</span>';
		    $datum[7] = '<i class="fa fa-times"></i><span class="sr-only">'.I18N::translate('None').'</span>';
		    if($ga->hasMap()) {
		        $datum[6] = $options->getMap()->getDescription();
		        $datum[7] = '<span data-toggle="tooltip" title="' . $options->getMap()->getTopLevelName() . '" />';
		        $top_level = $options->getMapLevel();
		        if($place_hierarchy['type'] == 'header') {
		            $datum[7] .= $place_hierarchy['hierarchy'][$top_level - 1];
		        } else {
		            $datum[7] .= $top_level . '(' . $place_hierarchy['hierarchy'][$top_level - 1] . ')';
		        }
		        $datum[7] .= '</span>';
		    }
		    $datum[8] = $options->isUsingFlags() ? 
				'<i class="fa fa-check"></i><span class="sr-only">'.I18N::translate('Yes').'</span>' : 
				'<i class="fa fa-times"></i><span class="sr-only">'.I18N::translate('No').'</span>';
		    $datum[9] = $options->getMaxDetailsInGen() > 0 ? $options->getMaxDetailsInGen() : I18N::translate('All');
		    
		    $data[] = $datum;
        }
        
        $controller->pageHeader();
        
        echo \Zend_Json::encode(array(
            'draw'            => Filter::getInteger('draw'),
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data
        ));
        
    }

    /**
     * AdminConfig@edit
     */
    public function edit() {
        $ga_id = Filter::getInteger('ga_id');
        $ga = $this->provider->getGeoAnalysis($ga_id, false);
        
        $this->renderEdit($ga);
    }
    
    /**
     * AdminConfig@add
     */
    public function add() {
        $this->renderEdit(null);
    }
    
    /**
     * AdminConfig@save
     */
    public function save() {
        global $WT_TREE;
        
        $tmp_contrl = new PageController();
        $tmp_contrl->restrictAccess(
            Auth::isManager($WT_TREE) 
            && Filter::checkCsrf()
         );
        
        $ga_id          = Filter::postInteger('ga_id');
        $description    = Filter::post('description');
        $analysislevel  = Filter::postInteger('analysislevel');
        $use_map        = Filter::postBool('use_map');
        if($use_map) {
            $map_file   = base64_decode(Filter::post('map_file'));
            $map_top_level   = Filter::postInteger('map_top_level');
        }
        $use_flags      = Filter::postBool('use_flags');
        $gen_details    = Filter::postInteger('gen_details');
        
        $success = false; 
        if($ga_id) {
            $ga = $this->provider->getGeoAnalysis($ga_id, false);
            if($ga) {
                $ga->setTitle($description);
                $ga->setAnalysisLevel($analysislevel + 1);
                $options = $ga->getOptions();
                if($options) {
                    $options->setIsUsingFlags($use_flags);
                    $options->setMaxDetailsInGen($gen_details);
                    if($use_map) {
                        $options->setMap(new OutlineMap($map_file));
                        $options->setMapLevel($map_top_level + 1);
                    }
                    else {
                        $options->setMap(null);
                    }
                }
				
				$res = $this->provider->updateGeoAnalysis($ga);
				if($res) {
					FlashMessages::addMessage(I18N::translate('The geographical dispersion analysis “%s” has been successfully updated', $res->getTitle()), 'success');
					Log::addConfigurationLog('Module '.$this->module->getName().' : Geo Analysis ID “'.$res->getId().'” has been updated.');
					$ga = $res;
					$success = true;
				}
				else {
					FlashMessages::addMessage(I18N::translate('An error occured while updating the geographical dispersion analysis “%s”', $ga->getTitle()), 'danger');
					Log::addConfigurationLog('Module '.$this->module->getName().' : Geo Analysis ID “'. $ga->getId() .'” could not be updated. See error log.');
				}
            }
        } else {
			$ga = $this->provider->createGeoAnalysis(
				$description,
				$analysislevel + 1,
				$use_map ? $map_file : null,
				$use_map ? $map_top_level + 1 : null,
				$use_flags,
				$gen_details
			);
			if($ga) {
				FlashMessages::addMessage(I18N::translate('The geographical dispersion analysis “%s” has been successfully added.', $ga->getTitle()), 'success');
				Log::addConfigurationLog('Module '.$this->module->getName().' : Geo Analysis ID “'.$ga->getId().'” has been added.');
				$success = true;
			}
			else {
				FlashMessages::addMessage(I18N::translate('An error occured while adding the geographical dispersion analysis “%s”', $description), 'danger');
				Log::addConfigurationLog('Module '.$this->module->getName().' : Geo Analysis “'.$description.'” could not be added. See error log.');
			}
        }
        
        $redirection_url = 'module.php?mod=' . $this->module->getName() . '&mod_action=AdminConfig&ged=' . $WT_TREE->getNameUrl();
        if(!$success) {			
            if($ga) {
                $redirection_url = 'module.php?mod=' . $this->module->getName() . '&mod_action=AdminConfig@edit&ga_id='. $ga->getId() .'&ged=' . $WT_TREE->getNameUrl();
            }
            else {
                $redirection_url = 'module.php?mod=' . $this->module->getName() . '&mod_action=AdminConfig@add&ged=' . $WT_TREE->getNameUrl();
            }
        }        
        header('Location: ' . WT_BASE_URL . $redirection_url);
        
    }
     
	/**
	 * Renders the edit form, whether it is an edition of an existing GeoAnalysis, or the addition of a new one.
	 * 
	 * @param (GeoAnalysis!null) $ga GeoAnalysis to edit
	 */
    protected function renderEdit(GeoAnalysis $ga = null) {
        global $WT_TREE;
        
        Theme::theme(new AdministrationTheme)->init($WT_TREE);
        $controller = new PageController();        
        $controller
            ->restrictAccess(Auth::isManager($WT_TREE))
            ->addInlineJavascript('
                function toggleMapOptions() {
                    if($("input:radio[name=\'use_map\']:checked").val() == 1) {
                        $("#map_options").show();
                    }
                    else {
                        $("#map_options").hide();
                    }
                }
        
                $("[name=\'use_map\']").on("change", toggleMapOptions);
                toggleMapOptions();
            ');
        
        $data = new ViewBag();
        if($ga) {
            $controller->setPageTitle(I18N::translate('Edit the geographical dispersion analysis'));
            $data->set('geo_analysis', $ga);
        } else {
            $controller->setPageTitle(I18N::translate('Add a geographical dispersion analysis'));
        }
        
        $data->set('title', $controller->getPageTitle());
        $data->set('admin_config_url', 'module.php?mod=' . $this->module->getName() . '&mod_action=AdminConfig&ged=' . $WT_TREE->getNameUrl());
        $data->set('module_title', $this->module->getTitle());
        $data->set('save_url', 'module.php?mod=' . $this->module->getName() . '&mod_action=AdminConfig@save&ged=' . $WT_TREE->getNameUrl());
        $data->set('places_hierarchy', $this->provider->getPlacesHierarchy());
    
        $map_list = array_map(
            function($map) {
                return $map->getDescription();
            },
            $this->provider->getOutlineMapsList()
            );
        asort($map_list);
        $data->set('map_list', $map_list);
    
        $gen_details = array(0 => I18N::translate('All'));
        for($i = 1; $i <= 10 ; $i++) $gen_details[$i] = $i;
        $data->set('generation_details', $gen_details);
    
        ViewFactory::make('GeoAnalysisEdit', $this, $controller, $data)->render();
    }
    
}