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
namespace MyArtJaub\Webtrees\Module\WelcomeBlock;

use Fisharebest\Webtrees\I18N;
use MyArtJaub\Webtrees\Mvc\View\ViewFactory;
use MyArtJaub\Webtrees\Mvc\View\ViewBag;
use Fisharebest\Webtrees\Controller\PageController;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\AbstractModule;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Theme;
use Fisharebest\Webtrees\Filter;
use Fisharebest\Webtrees\Controller\BaseController;
use MyArtJaub\Webtrees\Mvc\Controller\MvcController;

/**
 * Controller for Lineage
 */
class WelcomeBlockController extends MvcController
{   
    
    /**
     * Pages
     */
        
    /**
     * WelcomeBlock@index
     * 
     * @param PageController $parent_controller
     * @param Tree $tree
     * @param string $block_id
     * @param string $template
     * @return $string
     */
    public function index(PageController $parent_controller, Tree $tree, $block_id, $template) {        
        $view_bag = new ViewBag();
        
        if($parent_controller && $tree) {
        
            $view_bag->set('tree', $tree);
            $view_bag->set('indi', $parent_controller->getSignificantIndividual());
        
            $id = $this->module->getName().$block_id;
            $class = $this->module->getName().'_block';
            $parent_controller->addInlineJavascript('
                jQuery("#maj-new_passwd").hide();
                jQuery("#maj-passwd_click").click(function()
                {
                    jQuery("#maj-new_passwd").slideToggle(100, function() {
                        jQuery("#maj-new_passwd_username").focus();
    			});
    					return false;
    				  });
    			');
    
            if (Auth::isAdmin()) {
                $title='<a class="icon-admin" title="'.I18N::translate('Configure').'" href="block_edit.php?block_id='.$block_id.'&amp;ged=' . $tree->getNameHtml() . '&amp;ctype=gedcom"></a>';
            } else {
                $title='';
            }
            $title .='<span dir="auto">'.$tree->getTitleHtml().'</span>';
    
            $piwik_enabled = $this->module->getBlockSetting($block_id, 'piwik_enabled', false);
            $view_bag->set('piwik_enabled', $piwik_enabled);
            if($piwik_enabled) {
                $parent_controller->addInlineJavascript(
                    '$("#piwik_stats")
                        .load("module.php?mod='.$this->module->getName().'&mod_action=Piwik&block_id='.$block_id.'");'
                );
            }
    
            $content = ViewFactory::make('WelcomeBlock', $this,  new BaseController(), $view_bag)->getHtmlPartial();   
            
            if ($template) {
                return Theme::theme()->formatBlock($id, $title, $class, $content);
            } else {
                return $content;
            }
        }
    }
    
    
    
    /**
     * WelcomeBlock@config
     * 
     * @param string $block_id
     */
    public function config($block_id) {

        if (Filter::postBool('save') && Filter::checkCsrf()) {
            $this->module->setBlockSetting($block_id, 'piwik_enabled', Filter::postBool('piwik_enabled'));
            $this->module->setBlockSetting($block_id, 'piwik_url', trim(Filter::postUrl('piwik_url')));
            $this->module->setBlockSetting($block_id, 'piwik_siteid', trim(Filter::post('piwik_siteid')));
            $this->module->setBlockSetting($block_id, 'piwik_token', trim(Filter::post('piwik_token')));
            exit;
        }
        
        $view_bag = new ViewBag();
        
        // Is Piwik Statistic Enabled ?
        $view_bag->set('piwik_enabled', $this->module->getBlockSetting($block_id, 'piwik_enabled', '0'));
        //Piwik Root Url
        $view_bag->set('piwik_url', $this->module->getBlockSetting($block_id, 'piwik_url', ''));
        // Piwik token
        $view_bag->set('piwik_token', $this->module->getBlockSetting($block_id, 'piwik_token', ''));
        // Piwik side id
        $view_bag->set('piwik_siteid', $this->module->getBlockSetting($block_id, 'piwik_siteid', ''));
        
        ViewFactory::make('WelcomeBlockConfig', $this, new BaseController(), $view_bag)->renderPartial();
    }
    
    
    
}