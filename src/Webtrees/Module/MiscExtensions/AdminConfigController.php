<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage MiscExtensions
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module\MiscExtensions;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Controller\PageController;
use Fisharebest\Webtrees\Filter;
use Fisharebest\Webtrees\FlashMessages;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Theme;
use Fisharebest\Webtrees\Theme\AdministrationTheme;
use MyArtJaub\Webtrees\Mvc\Controller\MvcController;
use MyArtJaub\Webtrees\Mvc\View\ViewBag;
use MyArtJaub\Webtrees\Mvc\View\ViewFactory;

/**
 * Controller for GeoDispersion AdminConfig
 */
class AdminConfigController extends MvcController
{    
    /**
     * Manage updates sent from the AdminConfig@index form.
     */
    protected function update() {
        global $WT_TREE;
    
        if(Auth::isAdmin()){
    
            $this->module->setSetting('MAJ_TITLE_PREFIX', Filter::post('MAJ_TITLE_PREFIX'));
            
            $this->module->setSetting('MAJ_ADD_HTML_HEADER', Filter::postInteger('MAJ_ADD_HTML_HEADER', 0, 1));
            $this->module->setSetting('MAJ_SHOW_HTML_HEADER', Filter::postInteger('MAJ_SHOW_HTML_HEADER', Auth::PRIV_HIDE, Auth::PRIV_PRIVATE, Auth::PRIV_HIDE));
            $this->module->setSetting('MAJ_HTML_HEADER', Filter::post('MAJ_HTML_HEADER'));
            
            $this->module->setSetting('MAJ_ADD_HTML_FOOTER', Filter::postInteger('MAJ_ADD_HTML_FOOTER', 0, 1));
            $this->module->setSetting('MAJ_SHOW_HTML_FOOTER', Filter::postInteger('MAJ_SHOW_HTML_FOOTER', Auth::PRIV_HIDE, Auth::PRIV_PRIVATE, Auth::PRIV_HIDE));
            $this->module->setSetting('MAJ_HTML_FOOTER', Filter::post('MAJ_HTML_FOOTER'));
            
            $this->module->setSetting('MAJ_DISPLAY_CNIL', Filter::postInteger('MAJ_DISPLAY_CNIL', 0, 1));
            $this->module->setSetting('MAJ_CNIL_REFERENCE', Filter::post('MAJ_CNIL_REFERENCE'));
                
            FlashMessages::addMessage(I18N::translate('The preferences for the module “%s” have been updated.', $this->module->getTitle()), 'success');
    
            return;
        }
    }
    
    /**
     * Pages
     */
        
    /**
     * AdminConfig@index
     */
    public function index() {      
        global $WT_TREE;
        
        $action = Filter::post('action');        
        if($action == 'update' && Filter::checkCsrf()) $this->update();
        
        Theme::theme(new AdministrationTheme)->init($WT_TREE);        
        $ctrl = new PageController();
        $ctrl
            ->restrictAccess(Auth::isAdmin())
            ->setPageTitle($this->module->getTitle());
            
        $view_bag = new ViewBag();
        $view_bag->set('title', $ctrl->getPageTitle());
        $view_bag->set('module', $this->module);
        
        ViewFactory::make('AdminConfig', $this, $ctrl, $view_bag)->render();
    }
}