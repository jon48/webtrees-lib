<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Certificates
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module\Certificates;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Controller\PageController;
use Fisharebest\Webtrees\File;
use Fisharebest\Webtrees\Filter;
use Fisharebest\Webtrees\FlashMessages;
use Fisharebest\Webtrees\Html;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Theme;
use Fisharebest\Webtrees\Theme\AdministrationTheme;
use MyArtJaub\Webtrees\Globals;
use MyArtJaub\Webtrees\Mvc\Controller\MvcController;
use MyArtJaub\Webtrees\Mvc\View\ViewBag;
use MyArtJaub\Webtrees\Mvc\View\ViewFactory;

/**
 * Controller for Certificates AdminConfig
 */
class AdminConfigController extends MvcController
{
    /**
     * Manage updates sent from the AdminConfig@index form.
     */
    protected function update() {
        
        if(Auth::isAdmin()){
            
            $this->module->setSetting('MAJ_SHOW_CERT', Filter::post('MAJ_SHOW_CERT'));
            $this->module->setSetting('MAJ_SHOW_NO_WATERMARK', Filter::post('MAJ_SHOW_NO_WATERMARK'));
            
            if($MAJ_WM_DEFAULT = Filter::post('MAJ_WM_DEFAULT')) {
                $this->module->setSetting('MAJ_WM_DEFAULT', $MAJ_WM_DEFAULT);
            }
            
            if($MAJ_WM_FONT_MAXSIZE = Filter::postInteger('MAJ_WM_FONT_MAXSIZE')) {
                $this->module->setSetting('MAJ_WM_FONT_MAXSIZE', $MAJ_WM_FONT_MAXSIZE);
            }
            
            // Only accept valid color for MAJ_WM_FONT_COLOR
            $MAJ_WM_FONT_COLOR = Filter::post('MAJ_WM_FONT_COLOR', '#([a-fA-F0-9]{3}){1,2}');            
            if($MAJ_WM_FONT_COLOR) {
                $this->module->setSetting('MAJ_WM_FONT_COLOR', $MAJ_WM_FONT_COLOR);
            }
            
            // Only accept valid folders for MAJ_CERT_ROOTDIR
            $MAJ_CERT_ROOTDIR = preg_replace('/[\/\\\\]+/', '/', Filter::post('MAJ_CERT_ROOTDIR') . '/');
            if (substr($MAJ_CERT_ROOTDIR, 0, 1) === '/') {
                $MAJ_CERT_ROOTDIR = substr($MAJ_CERT_ROOTDIR, 1);
            }
            
            if ($MAJ_CERT_ROOTDIR) {
                if (is_dir(WT_DATA_DIR . $MAJ_CERT_ROOTDIR)) {
                    $this->module->setSetting('MAJ_CERT_ROOTDIR', $MAJ_CERT_ROOTDIR);
                } elseif (File::mkdir(WT_DATA_DIR . $MAJ_CERT_ROOTDIR)) {
                    $this->module->setSetting('MAJ_CERT_ROOTDIR', $MAJ_CERT_ROOTDIR);
                    FlashMessages::addMessage(I18N::translate('The folder %s has been created.', Html::filename(WT_DATA_DIR . $MAJ_CERT_ROOTDIR)), 'info');
                } else {
                    FlashMessages::addMessage(I18N::translate('The folder %s does not exist, and it could not be created.', Html::filename(WT_DATA_DIR . $MAJ_CERT_ROOTDIR)), 'danger');
                }
            }
            
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
        $action = Filter::post('action');        
        if($action == 'update' && Filter::checkCsrf()) $this->update();
        
        Theme::theme(new AdministrationTheme)->init(Globals::getTree());        
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