<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Kinship
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module;

use Fisharebest\Webtrees\Database;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module;
use Fisharebest\Webtrees\Module\ModuleChartInterface;
use Fisharebest\Webtrees\Module\ModuleSidebarInterface;
use MyArtJaub\Webtrees\Module\Kinship\Model\Kinship;
use MyArtJaub\Webtrees\Module\Kinship\Model\TreeTopologyProvider;

/**
 * Sosa Module.
 */
class KinshipModule 
    extends AbstractModule 
    implements ModuleChartInterface, ModuleSidebarInterface
{
    // How to update the database schema for this module
    const SCHEMA_TARGET_VERSION   = 1;
    const SCHEMA_SETTING_NAME     = 'MAJ_RELA_SCHEMA_VERSION';
    const SCHEMA_MIGRATION_PREFIX = '\MyArtJaub\Webtrees\Module\Kinship\Schema';

    /** @var string For custom modules - link for support, upgrades, etc. */
    const CUSTOM_WEBSITE = 'https://github.com/jon48/webtrees-lib';
    
    /**
     * {@inhericDoc}
     */
    public function getTitle() {
        return /* I18N: Name of the “Kinship” module */ I18N::translate('Kinship and consanguinity');
    }
    
    /**
     * {@inhericDoc}
     */
    public function getDescription() {
        return /* I18N: Description of the “Kinship” module */ I18N::translate('Determine kinship and coefficient of relationship between individuals.');
    }
    
    /**
     * {@inhericDoc}
     */
    public function modAction($mod_action) {
        Database::updateSchema(self::SCHEMA_MIGRATION_PREFIX, self::SCHEMA_SETTING_NAME, self::SCHEMA_TARGET_VERSION);
        
        switch($mod_action) {
            case 'ajax':
                header('Content-Type: text/html; charset=UTF-8');
                echo $this->getSidebarAjaxContent();
                break;
            default:
                \MyArtJaub\Webtrees\Mvc\Dispatcher::getInstance()->handle($this, $mod_action);
        }
    }

    /**********
     * ModuleChartInterface
     **********/
    
     /**
      * {@inheritDoc}
      * @see \Fisharebest\Webtrees\Module\ModuleChartInterface::getChartMenu()
      */
     public function getChartMenu(Individual $individual) {
      // TODO: Auto-generated method stub
    
     }
    
     /**
      * {@inheritDoc}
      * @see \Fisharebest\Webtrees\Module\ModuleChartInterface::getBoxChartMenu()
      */
     public function getBoxChartMenu(Individual $individual) {
      // TODO: Auto-generated method stub
    
     }
     
     /**********
      * ModuleSidebarInterface
      **********/
    
     /**
      * {@inheritDoc}
      * @see \Fisharebest\Webtrees\Module\ModuleSidebarInterface::defaultSidebarOrder()
      */
     public function defaultSidebarOrder() {
      // TODO: Auto-generated method stub
    
     }
    
     /**
      * {@inheritDoc}
      * @see \Fisharebest\Webtrees\Module\ModuleSidebarInterface::getSidebarContent()
      */
     public function getSidebarContent() {
         global $WT_TREE, $controller;
          
         $indi = $controller->getSignificantIndividual();
         if($indi && $fam = $indi->getPrimaryChildFamily()) {
             if(($husb = $fam->getHusband()) && ($wife = $fam->getWife())) {
                 $kinship = new Kinship($husb, $wife, (new TreeTopologyProvider($WT_TREE))->getTreeTopology());
                 $res = $kinship->compute();
                 return '</p>' . I18N::translate('Consanguinity: %.3f %%', $res['kinship_coef'] * 100) . '</p>';
             }
         }
         return 'No calculation';
     }
    
     /**
      * {@inheritDoc}
      * @see \Fisharebest\Webtrees\Module\ModuleSidebarInterface::getSidebarAjaxContent()
      */
     public function getSidebarAjaxContent() {
         return '';
     }
    
     /**
      * {@inheritDoc}
      * @see \Fisharebest\Webtrees\Module\ModuleSidebarInterface::hasSidebarContent()
      */
     public function hasSidebarContent() {
         return true;
     }

}
 