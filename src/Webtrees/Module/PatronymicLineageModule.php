<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage PatronymicLineage
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module;

use \Fisharebest\Webtrees as fw;
use \Fisharebest\Webtrees\I18N;
use \MyArtJaub\Webtrees\Mvc\Dispatcher;

/**
 * Patronymic Lineage Module.
 */
class PatronymicLineageModule extends fw\Module\AbstractModule 
    implements ModuleMenuItemInterface
{
        
    /**
     * {@inhericDoc}
     */
    public function getTitle() {
        return /* I18N: Name of the “Patronymic lineage” module */ I18N::translate('Patronymic Lineages');
    }
    
    /**
     * {@inhericDoc}
     */
    public function getDescription() {
        return /* I18N: Description of the “Patronymic lineage” module */ I18N::translate('Display lineages of people holding the same surname.');
    }
    
    /**
     * {@inhericDoc}
     */
    public function modAction($mod_action) {
        \MyArtJaub\Webtrees\Mvc\Dispatcher::getInstance()->handle($this, $mod_action);
    }
     /** 
      * {@inhericDoc}
      * @see \MyArtJaub\Webtrees\Module\ModuleMenuItemInterface::getMenu()
      */
     public function getMenu(fw\Tree $tree, $reference) {
         $tree_url = $tree ? $tree->getNameUrl() : '';
         $surname = $reference && is_string($reference) ? $reference : '';
         return new fw\Menu($this->getTitle(), 'module.php?mod=' . $this->getName() . '&mod_action=Lineage&ged=' . $tree_url . '&surname=' . $surname , 'menu-maj-list-lineage', array('rel' => 'nofollow'));
     }

}
 