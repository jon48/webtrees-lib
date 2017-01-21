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
namespace MyArtJaub\Webtrees\Module\Sosa;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Controller\AjaxController;
use Fisharebest\Webtrees\Controller\PageController;
use Fisharebest\Webtrees\Date;
use Fisharebest\Webtrees\Family;
use Fisharebest\Webtrees\Filter;
use Fisharebest\Webtrees\Functions\FunctionsPrintLists;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Stats;
use MyArtJaub\Webtrees\Constants;
use MyArtJaub\Webtrees\Functions\Functions;
use MyArtJaub\Webtrees\Functions\FunctionsPrint;
use MyArtJaub\Webtrees\Module\ModuleManager;
use MyArtJaub\Webtrees\Module\Sosa\Model\SosaProvider;
use MyArtJaub\Webtrees\Mvc\Controller\MvcController;
use MyArtJaub\Webtrees\Mvc\View\ViewBag;
use MyArtJaub\Webtrees\Mvc\View\ViewFactory;
use Rhumsaa\Uuid\Uuid;

/**
 * Controller for SosaList
 */
class SosaListController extends MvcController
{
    /**
     * Sosa Provider for the controller
     * @var SosaProvider $sosa_provider
     */
    protected $sosa_provider;
    
    /**
     * Generation used for the controller
     * @var int $generation
     */
    protected $generation;
    
    /**
     * ViewBag to hold data for the controller
     * @var ViewBag $view_bag
     */
    protected $view_bag;
    
    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Mvc\Controller\MvcController::__construct(AbstractModule $module)
     */
    public function __construct(AbstractModule $module) {
        global $WT_TREE;
        
        parent::__construct($module);

        $this->sosa_provider = new SosaProvider($WT_TREE, Auth::user());

        $this->generation = Filter::getInteger('gen');
        
        $this->view_bag = new ViewBag();
        $this->view_bag->set('generation', $this->generation);
        $this->view_bag->set('max_gen', $this->sosa_provider->getLastGeneration());
        $this->view_bag->set('is_setup', $this->sosa_provider->isSetup() && $this->view_bag->get('max_gen', 0)> 0);
        
    }
    
    
    /**
     * Pages
     */
    
    /**
     * SosaList@index
     */
    public function index() {
        global $WT_TREE;
        
        $controller = new PageController();
        $controller
            ->setPageTitle(I18N::translate('Sosa Ancestors'));            

        $this->view_bag->set('title', $controller->getPageTitle());
        
        if($this->view_bag->get('is_setup', false)) {
            $this->view_bag->set('has_sosa', $this->generation > 0 && $this->sosa_provider->getSosaCountAtGeneration($this->generation) > 0);
            $this->view_bag->set('url_module', $this->module->getName());
            $this->view_bag->set('url_action', 'SosaList');
            $this->view_bag->set('url_ged', $WT_TREE->getNameUrl()); 
            $this->view_bag->set('min_gen', 1);
            
            if($this->view_bag->get('has_sosa', false)) {            
                $controller->addInlineJavascript('
            		jQuery("#sosalist-tabs").tabs();
            		jQuery("#sosalist-tabs").css("visibility", "visible");
                
            		jQuery.get(
            			"module.php",
            			{
                            "mod" : "'.$this->module->getName().'",
                            "mod_action": "SosaList@sosalist",
                            "ged" : "' . $WT_TREE->getNameUrl(). '",
                            "type" : "indi",
                            "gen" : "'.$this->generation.'"
                        },
            			"html"
            		).success(
            			function(data){
            				if(data){
            					jQuery("#sosalist-indi-data").html(data);
            					/* datatablesosaindi(); */
            			    }
            			    jQuery("#loading-indi").hide();
            			}
            		).error(
            			function(){
            				jQuery("#sosalist-indi-data").html("'. Filter::escapeJs('<p class="warning">'.I18N::translate('An error occurred while retrieving data...').'</p>').'");
            			    jQuery("#loading-indi").hide();
            			}
            		);
                
            		jQuery.get(
            			"module.php",
                        {
                            "mod" : "'.$this->module->getName().'",
                            "mod_action": "SosaList@sosalist",
                            "ged" : "' . $WT_TREE->getNameUrl(). '",
                            "type" : "fam",
                            "gen" : "'.$this->generation.'"
                        },
            			"html"
            		).success(
            			function(data){
            				if(data){
            					jQuery("#sosalist-fam-data").html(data);
            			    }
            			    jQuery("#loading-fam").hide();
            			}
            		).error(
            			function(){
            				jQuery("#sosalist-fam-data").html("'.Filter::escapeJs('<p class="warning">'.I18N::translate('An error occurred while retrieving data...').'</p>').'");
            			    jQuery("#loading-fam").hide();
            			}
            		);
                
            	');            
            }
        }
                
        ViewFactory::make('SosaList', $this, $controller, $this->view_bag)->render();   
    }    
    

    /**
     * SosaList@missing
     */
    public function missing() {
        global $WT_TREE;
        
        $controller = new PageController();
        $controller
        ->setPageTitle(I18N::translate('Missing Ancestors'));
        
        $this->view_bag->set('title', $controller->getPageTitle());
        
        if($this->view_bag->get('is_setup', false)) {
            $this->view_bag->set('url_module', $this->module->getName());
            $this->view_bag->set('url_action', 'SosaList@missing');
            $this->view_bag->set('url_ged', $WT_TREE->getNameUrl());
            $this->view_bag->set('min_gen', 2);
            
            $missing_list = $this->sosa_provider->getMissingSosaListAtGeneration($this->generation);
            $this->view_bag->set('has_missing', $this->generation > 0 && count($missing_list) > 0);
            
            $perc_sosa = Functions::safeDivision($this->sosa_provider->getSosaCountAtGeneration($this->generation), pow(2, $this->generation -1));
            $this->view_bag->set('perc_sosa', $perc_sosa);
            
            if($this->view_bag->get('has_missing', false)) {
                $table_id = 'table-sosa-missing-' . Uuid::uuid4();
                $this->view_bag->set('table_id', $table_id);
                
                $controller
                ->addExternalJavascript(WT_JQUERY_DATATABLES_JS_URL)
                ->addInlineJavascript('
				    jQuery.fn.dataTableExt.oSort["text-asc"] = textCompareAsc;
				    jQuery.fn.dataTableExt.oSort["text-desc"] = textCompareDesc;
                    
    				jQuery("#'.$table_id.'").dataTable( {
                        dom: \'<"H"<"filtersH_' . $table_id . '">T<"dt-clear">pf<"dt-clear">irl>t<"F"pl<"dt-clear"><"filtersF_' . $table_id . '">>\',
    					'.I18N::datatablesI18N().',
    					jQueryUI: true,
    					autoWidth:false,
    					processing: true,
    					retrieve: true,
    					columns: [
    						/* 0-Sosa        */   { type: "num", class: "center" },
    		                /* 1-ID          */   { type: "text", class: "center" },
    		                /* 2-Given names */   { type: "text",  class: "left"},
    						/* 3-Surnames    */   { type: "text" },
    		                /* PERSO Modify table to include IsSourced module */
    		                /* 4-Indi source */	  { class: "center", visible: '.(ModuleManager::getInstance()->isOperational(Constants::MODULE_MAJ_ISSOURCED_NAME) ? 'true' : 'false').' },
    		                /* 5-Father      */	  { type: "text", class: "center"},
    		                /* 6-Mother      */   { type: "text", class: "center"},
    		                /* 7-Birth date  */	  { type: "num", class: "center"},
    		                /* 8-Birth place */	  { type: "text", class: "center"},
    		                /* 9-Birth source*/	  { class: "center", visible: '.(ModuleManager::getInstance()->isOperational(Constants::MODULE_MAJ_ISSOURCED_NAME) ? 'true' : 'false').' },
    		                /* 10-Filter sex */   { sortable: false },
    		                /* END PERSO */
    					],
    		            sorting: [[0,"asc"]],
    					displayLength: 20,
    					pagingType: "full_numbers"
    			   });
    			
    				jQuery("#' . $table_id . '")
    				/* Filter buttons in table header */
    				.on("click", "button[data-filter-column]", function() {
    					var btn = jQuery(this);
    					// De-activate the other buttons in this button group
    					btn.siblings().removeClass("ui-state-active");
    					// Apply (or clear) this filter
    					var col = jQuery("#' . $table_id . '").DataTable().column(btn.data("filter-column"));
    					if (btn.hasClass("ui-state-active")) {
    						btn.removeClass("ui-state-active");
    						col.search("").draw();
    					} else {
    						btn.addClass("ui-state-active");
    						col.search(btn.data("filter-value")).draw();
    					}
    				});
                    
    				jQuery(".smissing-list").css("visibility", "visible");
    				jQuery(".loading-image").css("display", "none");
    			');
                        
                $unique_indis = array();
                $sum_missing_different = 0;
                $sum_missing_different_without_hidden = 0;
                foreach($missing_list as $num => $missing_tab) {
                    if(isset($unique_indis[$missing_tab['indi']])) {
                        unset($missing_list[$num]);
                        continue;
                    }
                    $sum_missing_different += !$missing_tab['has_father'] + !$missing_tab['has_mother'];
                    $person = Individual::getInstance($missing_tab['indi'], $WT_TREE);
                    if (!$person || !$person->canShowName()) {
                        unset($missing_list[$num]);
                        continue;
                    }  
                    $sum_missing_different_without_hidden += !$missing_tab['has_father'] + !$missing_tab['has_mother'];
                    $unique_indis[$person->getXref()] = true;
                    $missing_tab['indi'] = $person;
                    $missing_list[$num] = $missing_tab;
                }
                $this->view_bag->set('missing_list', $missing_list);
                $this->view_bag->set('missing_diff_count', $sum_missing_different);
                $this->view_bag->set('missing_hidden', $sum_missing_different - $sum_missing_different_without_hidden);
                $perc_sosa_potential = Functions::safeDivision($this->sosa_provider->getSosaCountAtGeneration($this->generation - 1), pow(2, $this->generation-2));
                $this->view_bag->set('perc_sosa_potential', $perc_sosa_potential);
            }            
        }
        
        ViewFactory::make('SosaListMissing', $this, $controller, $this->view_bag)->render();
    }
    
    /**
     * SosaList@sosalist
     */
    public function sosalist() {
                
        $type = Filter::get('type', 'indi|fam', null);
        
        $controller = new AjaxController();
        $controller->restrictAccess($this->generation > 0 || !is_null($type));
        
        switch ($type){
            case 'indi':
                $this->renderSosaListIndi($controller);
                break;
            case 'fam':
                $this->renderFamSosaListIndi($controller);
                break;
            default:
                break;
        }

    }
    
    /**
     * Render the Ajax response for the sortable table of Sosa individuals
     * @param AjaxController $controller
     */
    protected function renderSosaListIndi(AjaxController $controller) {
        global $WT_TREE;
        
        $listSosa = $this->sosa_provider->getSosaListAtGeneration($this->generation); 
        $this->view_bag->set('has_sosa', false);
        
        if(count($listSosa) > 0) {
            $this->view_bag->set('has_sosa', true);
            $table_id = 'table-sosa-indi-' . Uuid::uuid4();
            $this->view_bag->set('table_id', $table_id);
                     
            $controller
            ->addExternalJavascript(WT_JQUERY_DATATABLES_JS_URL)
            ->addInlineJavascript('
                jQuery.fn.dataTableExt.oSort["text-asc"] = textCompareAsc;
                jQuery.fn.dataTableExt.oSort["text-desc"] = textCompareDesc;
                
                jQuery("#'.$table_id.'").dataTable( {
					dom: \'<"H"<"filtersH_' . $table_id . '">T<"dt-clear">pf<"dt-clear">irl>t<"F"pl<"dt-clear"><"filtersF_' . $table_id . '">>\',
					' . I18N::datatablesI18N() . ',
					jQueryUI: true,
					autoWidth: false,
					processing: true,
					retrieve: true,
					columns: [
						/* 0-Sosa          */     { type: "num", class: "center" },
		                /* 1-ID            */     { type: "text", sortable: false },
		                /* 2-Given names   */     { type: "text" },
						/* 3-Surnames      */     { type: "text" },
		                /* 4-Birth date    */     { type: "num", class: "center"},
		                /* 5-Birth place   */     { type: "text", class: "center"},
		                /* PERSO Modify table to include IsSourced module */
		                /* 6-Birth source  */     { class: "center", visible: '.(ModuleManager::getInstance()->isOperational(Constants::MODULE_MAJ_ISSOURCED_NAME) ? 'true' : 'false').' },
		                /* 7-Death date    */     { type: "num", class: "center"},
		                /* 8-Age           */     { type: "num", class: "center"},
		                /* 9-Death place   */     { type: "text", class: "center" },
		                /* 10-Death source */     { class: "center", visible: '.(ModuleManager::getInstance()->isOperational(Constants::MODULE_MAJ_ISSOURCED_NAME) ? 'true' : 'false').' },
						/* 11-Filter sex   */     { sortable: false },
						/* 12-Filter birth */     { sortable: false },
						/* 13-Filter death */     { sortable: false },
						/* 14-Filter tree  */     { sortable: false }
		                /* END PERSO */
					],
		            sorting: [[0,"asc"]],
					displayLength: 16,
					pagingType: "full_numbers"
			   });
            
				jQuery("#' . $table_id . '")
				/* Hide/show parents */
				.on("click", ".btn-toggle-parents", function() {
					jQuery(this).toggleClass("ui-state-active");
					jQuery(".parents", jQuery(this).closest("table").DataTable().rows().nodes()).slideToggle();
				})
				/* Hide/show statistics */
				.on("click", ".btn-toggle-statistics", function() {
					jQuery(this).toggleClass("ui-state-active");
					jQuery("#indi_list_table-charts_' . $table_id . '").slideToggle();
				})
				/* Filter buttons in table header */
				.on("click", "button[data-filter-column]", function() {
					var btn = jQuery(this);
					// De-activate the other buttons in this button group
					btn.siblings().removeClass("ui-state-active");
					// Apply (or clear) this filter
					var col = jQuery("#' . $table_id . '").DataTable().column(btn.data("filter-column"));
					if (btn.hasClass("ui-state-active")) {
						btn.removeClass("ui-state-active");
						col.search("").draw();
					} else {
						btn.addClass("ui-state-active");
						col.search(btn.data("filter-value")).draw();
					}
				});
            
				jQuery("#sosa-indi-list").css("visibility", "visible");
		
				jQuery("#btn-toggle-statistics-'.$table_id.'").click();
           ');
            
            $stats = new Stats($WT_TREE);         
            
            // Bad data can cause "longest life" to be huge, blowing memory limits
            $max_age = min($WT_TREE->getPreference('MAX_ALIVE_AGE'), $stats->LongestLifeAge()) + 1;
            // Inititialise chart data
            $deat_by_age = array();
            for ($age = 0; $age <= $max_age; $age++) {
                $deat_by_age[$age] = '';
            }
            $birt_by_decade = array();
            $deat_by_decade = array();
            for ($year = 1550; $year < 2030; $year += 10) {
                $birt_by_decade[$year] = '';
                $deat_by_decade[$year] = '';
            }
            
            $unique_indis = array(); // Don't double-count indis with multiple names.
            $nb_displayed = 0;
            
            Individual::load($WT_TREE, $listSosa);
            foreach($listSosa as $sosa => $pid) {
                $person = Individual::getInstance($pid, $WT_TREE);
                if (!$person || !$person->canShowName()) {
                    unset($listSosa[$sosa]);
                    continue;
                }
                $nb_displayed++;
                if ($birth_dates=$person->getAllBirthDates()) {
                    if (
                        FunctionsPrint::isDateWithinChartsRange($birth_dates[0]) &&
                        !isset($unique_indis[$person->getXref()])
                        ) {
                        $birt_by_decade[(int)($birth_dates[0]->gregorianYear()/10)*10] .= $person->getSex();
                    }
                }
                else {
                    $birth_dates[0]=new Date('');
                }
                if ($death_dates = $person->getAllDeathDates()) {
                    if (
                        FunctionsPrint::isDateWithinChartsRange($death_dates[0]) &&
                        !isset($unique_indis[$person->getXref()])
                        ) {
                        $deat_by_decade[(int) ($death_dates[0]->gregorianYear() / 10) * 10] .= $person->getSex();
                    }
                }
                else {
                    $death_dates[0] = new Date('');
                }
                $age = Date::getAge($birth_dates[0], $death_dates[0], 0);
                if (!isset($unique_indis[$person->getXref()]) && $age >= 0 && $age <= $max_age) {
                    $deat_by_age[$age] .= $person->getSex();
                }
                $listSosa[$sosa] = $person;
                $unique_indis[$person->getXref()] = true;
            }
            $this->view_bag->set('sosa_list', $listSosa);   
            
            $this->view_bag->set('sosa_count', count($listSosa));
            $this->view_bag->set('sosa_theo', pow(2, $this->generation-1));
            $this->view_bag->set('sosa_ratio', Functions::safeDivision($this->view_bag->get('sosa_count'), $this->view_bag->get('sosa_theo')));
            
            $this->view_bag->set('sosa_hidden', $this->view_bag->get('sosa_count') - $nb_displayed);
            
            $this->view_bag->set('chart_births', FunctionsPrintLists::chartByDecade($birt_by_decade, I18N::translate('Decade of birth')));
            $this->view_bag->set('chart_deaths', FunctionsPrintLists::chartByDecade($deat_by_decade, I18N::translate('Decade of death')));
            $this->view_bag->set('chart_ages', FunctionsPrintLists::chartByAge($deat_by_age, I18N::translate('Age related to death year')));
        }
        
        ViewFactory::make('SosaListIndi', $this, $controller, $this->view_bag)->render();        
    }
    
    /**
     * Render the Ajax response for the sortable table of Sosa family
     * @param AjaxController $controller
     */
    protected function renderFamSosaListIndi(AjaxController $controller) {
        global $WT_TREE;
        
        $listFamSosa = $this->sosa_provider->getFamilySosaListAtGeneration($this->generation);;
        $this->view_bag->set('has_sosa', false);
        
        if(count($listFamSosa) > 0) {
            $this->view_bag->set('has_sosa', true);
            $table_id = 'table-sosa-fam-' . Uuid::uuid4();
            $this->view_bag->set('table_id', $table_id);
             
            $controller
            ->addExternalJavascript(WT_JQUERY_DATATABLES_JS_URL)
            ->addInlineJavascript('
				jQuery.fn.dataTableExt.oSort["text-asc"] = textCompareAsc;
				jQuery.fn.dataTableExt.oSort["text-desc"] = textCompareDesc;
                
                jQuery("#'.$table_id.'").dataTable( {
					dom: \'<"H"<"filtersH_' . $table_id . '"><"dt-clear">pf<"dt-clear">irl>t<"F"pl<"dt-clear"><"filtersF_' . $table_id . '">>\',
                    '.I18N::datatablesI18N(array(16, 32, 64, 128, -1)).',
					jQueryUI: true,
					autoWidth: false,
					processing: true,
					retrieve: true,
					columns: [
						/* 0-Sosa            */   { type: "num", class: "center"},
						/* 1-Given names     */   { type: "text" },
						/* 2-Surnames        */   { type: "text" },
						/* 3-Age             */   { type: "num", class: "center"},
						/* 4-Given names     */   { type: "text" },
						/* 5-Surnames        */   { type: "text" },
						/* 6-Age             */   { type: "num", class: "center"},
						/* 7-Marriage date   */   { type: "num", class: "center" },
						/* 8-Marriage place  */   { type: "text" },
                        /* 9-Marriage source */   { class: "center", visible: '.(ModuleManager::getInstance()->isOperational(Constants::MODULE_MAJ_ISSOURCED_NAME) ? 'true' : 'false').' },
						/* 10-Children       */   { type: "num" },
						/* 11-Filter marriage*/   { sortable: false },
						/* 12-Filter dead    */   { sortable: false },
						/* 13-Filter tree    */   { sortable: false }
					],
					sorting: [[0, "asc"]],
					displayLength: 16,
					pagingType: "full_numbers"
			   });
					
				jQuery("#' . $table_id . '")
				/* Hide/show parents */
				.on("click", ".btn-toggle-parents", function() {
					jQuery(this).toggleClass("ui-state-active");
					jQuery(".parents", jQuery(this).closest("table").DataTable().rows().nodes()).slideToggle();
				})
				/* Hide/show statistics */
				.on("click",  ".btn-toggle-statistics", function() {
					jQuery(this).toggleClass("ui-state-active");
					jQuery("#fam_list_table-charts_' . $table_id . '").slideToggle();
				})
				/* Filter buttons in table header */
				.on("click", "button[data-filter-column]", function() {
					var btn = $(this);
					// De-activate the other buttons in this button group
					btn.siblings().removeClass("ui-state-active");
					// Apply (or clear) this filter
					var col = jQuery("#' . $table_id . '").DataTable().column(btn.data("filter-column"));
					if (btn.hasClass("ui-state-active")) {
						btn.removeClass("ui-state-active");
						col.search("").draw();
					} else {
						btn.addClass("ui-state-active");
						col.search(btn.data("filter-value")).draw();
					}
				});					
				
				jQuery("#sosa-fam-list").css("visibility", "visible");
				
				jQuery("#btn-toggle-statistics-'.$table_id.'").click();
           ');
        
            $stats = new Stats($WT_TREE);        
            $max_age = max($stats->oldestMarriageMaleAge(), $stats->oldestMarriageFemaleAge()) + 1;
            
            //-- init chart data
    		$marr_by_age = array();
    		for ($age=0; $age<=$max_age; $age++) {
    			$marr_by_age[$age] = '';
    		}
    		$birt_by_decade = array();
    		$marr_by_decade = array();
    		for ($year=1550; $year<2030; $year+=10) {
    			$birt_by_decade[$year] = '';
    			$marr_by_decade[$year] = '';
    		}
    		
            foreach($listFamSosa as $sosa => $fid) {
                $sfamily = Family::getInstance($fid, $WT_TREE);
                if(!$sfamily || !$sfamily->canShow()) {
                    unset($sfamily[$sosa]);
                    continue;
                }
                $mdate=$sfamily->getMarriageDate();
                
                if( ($husb = $sfamily->getHusband()) && 
                    ($hdate = $husb->getBirthDate()) && 
                    $hdate->isOK() && $mdate->isOK()) {
                    if (FunctionsPrint::isDateWithinChartsRange($hdate)) {
                        $birt_by_decade[(int) ($hdate->gregorianYear() / 10) * 10] .= $husb->getSex();
                    }
                    $hage = Date::getAge($hdate, $mdate, 0);
                    if ($hage >= 0 && $hage <= $max_age) {
                        $marr_by_age[$hage] .= $husb->getSex();
                    }
                }
                
                if(($wife = $sfamily->getWife()) &&
                    ($wdate=$wife->getBirthDate()) &&
                    $wdate->isOK() && $mdate->isOK()) {
                    if (FunctionsPrint::isDateWithinChartsRange($wdate)) {
                        $birt_by_decade[(int) ($wdate->gregorianYear() / 10) * 10] .= $wife->getSex();
                    }
                    $wage = Date::getAge($wdate, $mdate, 0);
                    if ($wage >= 0 && $wage <= $max_age) {
                        $marr_by_age[$wage] .= $wife->getSex();
                    }
                }                

                if ($mdate->isOK() && FunctionsPrint::isDateWithinChartsRange($mdate) && $husb && $wife) {
                    $marr_by_decade[(int) ($mdate->gregorianYear() / 10) * 10] .= $husb->getSex() . $wife->getSex();
                }
                
                $listFamSosa[$sosa] = $sfamily;
            }
            $this->view_bag->set('sosa_list', $listFamSosa);
        
            $this->view_bag->set('chart_births', FunctionsPrintLists::chartByDecade($birt_by_decade, I18N::translate('Decade of birth')));
            $this->view_bag->set('chart_marriages', FunctionsPrintLists::chartByDecade($marr_by_decade, I18N::translate('Decade of marriage')));
            $this->view_bag->set('chart_ages', FunctionsPrintLists::chartByAge($marr_by_age, I18N::translate('Age in year of marriage')));
        }
        
        ViewFactory::make('SosaListFam', $this, $controller, $this->view_bag)->render();
    }
    
}