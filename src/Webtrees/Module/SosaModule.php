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
namespace MyArtJaub\Webtrees\Module;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Controller\IndividualController;
use Fisharebest\Webtrees\Database;
use Fisharebest\Webtrees\GedcomRecord;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Menu;
use Fisharebest\Webtrees\Module;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleConfigInterface;
use Fisharebest\Webtrees\Module\ModuleMenuInterface;
use MyArtJaub\Webtrees\Constants;
use MyArtJaub\Webtrees\Functions\FunctionsPrint;
use MyArtJaub\Webtrees\Globals;
use MyArtJaub\Webtrees\Hook\HookInterfaces\IndividualHeaderExtenderInterface;
use MyArtJaub\Webtrees\Hook\HookInterfaces\RecordNameTextExtenderInterface;
use MyArtJaub\Webtrees\Hook\HookSubscriberInterface;
use MyArtJaub\Webtrees\Individual;

/**
 * Sosa Module.
 */
class SosaModule 
    extends AbstractModule 
    implements  ModuleMenuInterface, 
                ModuleConfigInterface,
                HookSubscriberInterface,
                IndividualHeaderExtenderInterface,
                RecordNameTextExtenderInterface
{
    // How to update the database schema for this module
    const SCHEMA_TARGET_VERSION   = 1;
    const SCHEMA_SETTING_NAME     = 'MAJ_SOSA_SCHEMA_VERSION';
    const SCHEMA_MIGRATION_PREFIX = '\MyArtJaub\Webtrees\Module\Sosa\Schema';

    /** @var string For custom modules - link for support, upgrades, etc. */
    const CUSTOM_WEBSITE = 'https://github.com/jon48/webtrees-lib';
    
    /**
     * {@inhericDoc}
     */
    public function getTitle() {
        return /* I18N: Name of the “Sosa” module */ I18N::translate('Sosa');
    }
    
    /**
     * {@inhericDoc}
     */
    public function getDescription() {
        return /* I18N: Description of the “Sosa” module */ I18N::translate('Calculate and display Sosa ancestors of the root person.');
    }
    
    /**
     * {@inhericDoc}
     */
    public function modAction($mod_action) {
        Database::updateSchema(self::SCHEMA_MIGRATION_PREFIX, self::SCHEMA_SETTING_NAME, self::SCHEMA_TARGET_VERSION);
        
        \MyArtJaub\Webtrees\Mvc\Dispatcher::getInstance()->handle($this, $mod_action);
    }
    
    /**********
     * ModuleConfigInterface
     **********/
    
    /**
     * {@inhericDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleConfigInterface::getConfigLink()
     */
    public function getConfigLink() {
        Database::updateSchema(self::SCHEMA_MIGRATION_PREFIX, self::SCHEMA_SETTING_NAME, self::SCHEMA_TARGET_VERSION);
        
        return 'module.php?mod=' . $this->getName() . '&amp;mod_action=SosaConfig';
    }
    
    /**********
     * ModuleMenuInterface
     **********/
    
    /**
     * {@inhericDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleMenuInterface::defaultMenuOrder()
     */
    public function defaultMenuOrder() {
        return 5;
    }
    
    /**
     * {@inhericDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleMenuInterface::getMenu()
     */
    public function getMenu() { 
        $wt_tree = Globals::getTree();
        $menu = null;
        if(ModuleManager::getInstance()->isOperational($this->getName())) {
            
            $root_url = 'module.php?mod=' . $this->getName() . '&ged=' . $wt_tree->getNameUrl() . '&';
            $sosa_stat_menu = new Menu(I18N::translate('Sosa Statistics'), $root_url . 'mod_action=SosaStats', 'menu-maj-sosa-stats');
            
            $menu = clone $sosa_stat_menu;
            $menu->setClass('menu-maj-sosa');
            
            $submenus = array_filter(array(
                new Menu(I18N::translate('Sosa Ancestors'), $root_url . 'mod_action=SosaList', 'menu-maj-sosa-list', array('rel' => 'nofollow')),
                new Menu(I18N::translate('Missing Ancestors'), $root_url . 'mod_action=SosaList@missing', 'menu-maj-sosa-missing', array('rel' => 'nofollow')),
                $sosa_stat_menu                
            ));
            

            if (ModuleManager::getInstance()->isOperational(Constants::MODULE_MAJ_GEODISP_NAME)
                && $ga_list = Module::getModuleByName(Constants::MODULE_MAJ_GEODISP_NAME)->getProvider()->getGeoAnalysisList()
                )
            {
                if(count($ga_list) > 0) {
                    $submenus[] = new Menu(I18N::translate('Geographical Dispersion'), 'module.php?mod=' . Constants::MODULE_MAJ_GEODISP_NAME . '&ged=' . $wt_tree->getNameUrl() . '&mod_action=GeoAnalysis@listAll', 'menu-maj-sosa-geodispersion');
                }
            }
            
            if(Auth::check()) {
                $submenus[] = new Menu(
                    I18N::translate('Sosa Configuration'),
                    $this->getConfigLink(),
                    'menu-maj-sosa-configuration',
                    array('rel' => 'nofollow'));
            }
                        
            //-- recompute Sosa submenu
            $controller = Globals::getController();
            if (!empty($controller) && $controller instanceof IndividualController 
                && Auth::check() && $wt_tree->getUserPreference(Auth::user(), 'MAJ_SOSA_ROOT_ID')
                ) {
                $controller
                    ->addInlineJavascript('
                        function majComputeSosaFromIndi(){
                            if($("#computesosadlg").length == 0) {
                                $("body").append("<div id=\"computesosadlg\" title=\"'. I18N::translate('Sosas computation') .'\"><div id=\"sosaloadingarea\"></div></div>");
                            }
	                        $("#computesosadlg").dialog({
                                modal: true,
                                closeOnEscape: false,
		                        width: 300,
		                        open: function(event, ui) {
			                        $("button.ui-dialog-titlebar-close").hide();
                                    $("#sosaloadingarea").empty().html("<i class=\"icon-loading-small\"></i>");
			                        $("#sosaloadingarea").load("module.php?mod=' . $this->getName() . '&mod_action=SosaConfig@computePartial&ged='. $wt_tree->getNameUrl() .'&userid='.Auth::user()->getUserId().'&pid=' . $controller->getSignificantIndividual()->getXref() . '", 
					                   function(){
						                  $("button.ui-dialog-titlebar-close").show();
                                          setTimeout(function(){
                                            $("#computesosadlg").dialog("close");
                                          }, 2000);
			                            });		
		                          }
	                         });	
                        }');
                	
                $submenus[] = new Menu(
                    I18N::translate('Complete Sosas'), 
                    '#', 
                    'menu-maj-sosa-recompute', 
                    array(
                        'rel' => 'nofollow',
                        'onclick' => 'return majComputeSosaFromIndi();'
                    ));
            }
            
        }
        
        $menu->setSubmenus($submenus);
        
        return $menu;
        
    }
    
    /**********
     * Hooks
     **********/
    
    /**
     * {@inhericDoc}
     * @see \MyArtJaub\Webtrees\Hook\HookSubscriberInterface::getSubscribedHooks()
     */    
    public function getSubscribedHooks() {
        return array(
            'hExtendIndiHeaderIcons' => 20,
            'hExtendIndiHeaderRight' => 20,
            'hRecordNameAppend' => 20
        );
    }
    
    /**
     * {@inhericDoc}
     * @see \MyArtJaub\Webtrees\Hook\HookInterfaces\IndividualHeaderExtenderInterface::hExtendIndiHeaderIcons()
     */
    public function hExtendIndiHeaderIcons(IndividualController $ctrlIndi) {
        if($ctrlIndi){
            $dindi = new Individual($ctrlIndi->getSignificantIndividual());
            return FunctionsPrint::formatSosaNumbers($dindi->getSosaNumbers(), 1, 'large');
        }
        return '';
    }
    
    /**
     * {@inhericDoc}
     * @see \MyArtJaub\Webtrees\Hook\HookInterfaces\IndividualHeaderExtenderInterface::hExtendIndiHeaderLeft()
     */
    public function hExtendIndiHeaderLeft(IndividualController $ctrlIndi) { }
    
    /**
     * {@inhericDoc}
     * @see \MyArtJaub\Webtrees\Hook\HookInterfaces\IndividualHeaderExtenderInterface::hExtendIndiHeaderRight()
     */
    public function hExtendIndiHeaderRight(IndividualController $ctrlIndi) {
        if($ctrlIndi){
            $dindi = new Individual($ctrlIndi->getSignificantIndividual());
            return array('indi-header-sosa',  FunctionsPrint::formatSosaNumbers($dindi->getSosaNumbers(), 2, 'normal'));
        }
        return '';
    }
    
    /**
     * {@inhericDoc}
     * @see \MyArtJaub\Webtrees\Hook\HookInterfaces\RecordNameTextExtenderInterface::hRecordNameAppend()
     */
    public function hRecordNameAppend(GedcomRecord $grec, $size = 'small') {
        if($grec instanceof \Fisharebest\Webtrees\Individual){ // Only apply to individuals
            $dindi = new Individual($grec);
            return FunctionsPrint::formatSosaNumbers($dindi->getSosaNumbers(), 1, $size);
        }
        return '';
    }
    
    /**
     * {@inhericDoc}
     * @see \MyArtJaub\Webtrees\Hook\HookInterfaces\RecordNameTextExtenderInterface::hRecordNamePrepend()
     */
    public function hRecordNamePrepend(GedcomRecord $grec, $size) {}
    
    
    

}
 