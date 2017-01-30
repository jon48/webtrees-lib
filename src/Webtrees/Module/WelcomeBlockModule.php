<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage WelcomeBlock
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2011-2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module;

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleBlockInterface;
use MyArtJaub\Webtrees\Module\WelcomeBlock\WelcomeBlockController;
use MyArtJaub\Webtrees\Mvc\MvcException;

/**
 * Welcome Block Module.
 */
class WelcomeBlockModule extends AbstractModule
    implements ModuleBlockInterface
{
    /** @var string For custom modules - link for support, upgrades, etc. */
    const CUSTOM_WEBSITE = 'https://github.com/jon48/webtrees-lib';
        
    /**
     * {@inhericDoc}
     */
    public function getTitle() {
        return /* I18N: Name of the “WelcomeBlock” module */ I18N::translate('MyArtJaub Welcome Block');
    }
    
    /**
     * {@inhericDoc}
     */
    public function getDescription() {
        return /* I18N: Description of the “WelcomeBlock” module */ I18N::translate('The MyArtJaub Welcome block welcomes the visitor to the site, allows a quick login to the site, and displays statistics on visits.');
    }
    
    /**
     * {@inhericDoc}
     */
    public function modAction($mod_action) {
        \MyArtJaub\Webtrees\Mvc\Dispatcher::getInstance()->handle($this, $mod_action);
    }
    
    /**
     * {@inhericDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleBlockInterface::getBlock()
     */
	public function getBlock($block_id, $template = true, $cfg = array()) {
        global $controller, $WT_TREE;
        
        $wb_controller = new WelcomeBlockController($this);           
        return $wb_controller->index($controller, $WT_TREE, $block_id, $template);
    }
    
    /**
     * {@inhericDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleBlockInterface::loadAjax()
     */
    public function loadAjax() {
        return false;
    }
    
    /**
     * {@inhericDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleBlockInterface::isUserBlock()
     */
    public function isUserBlock() {
        return false;
    }
    
    /**
     * {@inhericDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleBlockInterface::isGedcomBlock()
     */
    public function isGedcomBlock() {
        return true;
    }
    
    /**
     * {@inhericDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleBlockInterface::configureBlock()
     */
    public function configureBlock($block_id) {
        $wb_controller = new WelcomeBlockController($this);
        try {
            return $wb_controller->config($block_id);
        }
        catch (MvcException $ex) {
            if($ex->getHttpCode() != 200) throw $ex;
            return;
        }     
    }

}
 