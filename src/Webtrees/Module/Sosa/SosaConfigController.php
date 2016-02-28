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

use Fisharebest\Webtrees\I18N;
use MyArtJaub\Webtrees\Mvc\View\ViewFactory;
use MyArtJaub\Webtrees\Mvc\View\ViewBag;
use Fisharebest\Webtrees\Controller\PageController;
use MyArtJaub\Webtrees\Mvc\Controller\MvcController;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\User;
use Fisharebest\Webtrees\Filter;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\FlashMessages;
use Fisharebest\Webtrees\Controller\BaseController;
use Fisharebest\Webtrees\Controller\AjaxController;
use MyArtJaub\Webtrees\Module\Sosa\Model\SosaCalculator;

/**
 * Controller for SosaConfig
 */
class SosaConfigController extends MvcController
{      
    /**
     * Check if the user can update the sosa ancestors list
     * 
     * @return bool
     */
    protected function canUpdate() {
        global $WT_TREE;
        
        $user_id = Filter::postInteger('userid', -1) ?: Filter::getInteger('userid', -1);
        return Auth::check() && 
            ( 
                $user_id == Auth::user()->getUserId() ||        // Allow update for yourself
                ($user_id == -1 && Auth::isManager($WT_TREE))   // Allow a manager to update the default user
             );
    }
    
    /**
     * Saves Sosa's user preferences (root individual for the user).
     * 
     * @param BaseController $controller
     * @return bool True is saving successfull
     */
    protected function update(BaseController $controller) {
        global $WT_TREE;          
        if($this->canUpdate() && Filter::checkCsrf()) 
        {            
            $indi = Individual::getInstance(Filter::post('rootid'), $WT_TREE);
            $user = User::find(Filter::postInteger('userid', -1));
            
            if($user  && $indi) {
                $WT_TREE->setUserPreference($user, 'MAJ_SOSA_ROOT_ID', $indi->getXref());
                $controller->addInlineJavascript('
                    $( document ).ready(function() {
                        majComputeSosa('.$user->getUserId().');
                    });');
                FlashMessages::addMessage(I18N::translate('The preferences have been updated.'));
                return true;
            }
        }
        FlashMessages::addMessage(I18N::translate('An error occurred while saving data...'), 'danger');
        return false;
    }
    
    /**
     * Pages
     */
    
    /**
     * SosaConfig@index
     */
    public function index() {
        global $WT_TREE;
        
        $controller = new PageController();
        $controller
        ->setPageTitle(I18N::translate('Sosa Configuration'))
        ->restrictAccess(Auth::check())
        ->addExternalJavascript(WT_AUTOCOMPLETE_JS_URL)
        ->addInlineJavascript('autocomplete();')
        ->addInlineJavascript('
            $( document ).ready(function() {
                $("#bt_sosa_compute").click(function() {
                    majComputeSosa($("#maj_sosa_input_userid, #maj-sosa-config-select option:selected").val());
                 });
            });
            
            function majComputeSosa(user_id) {
                jQuery("#bt_sosa_compute").prop( "disabled", true );
                jQuery("#bt_sosa_computing").empty().html("<i class=\"icon-loading-small\"></i>&nbsp;'. I18N::translate('Computing...') .'");
                jQuery("#bt_sosa_computing").load(
		          "module.php?mod='.$this->module->getName().'&mod_action=SosaConfig@computeAll&ged='.$WT_TREE->getNameUrl().'&userid=" + user_id,
		          function() {
			         jQuery("#bt_sosa_compute").prop( "disabled", false );
                  });
            }');
        
        $action = Filter::post('action');
        if($action === 'update') $this->update($controller);
        
        $view_bag = new ViewBag();
        $view_bag->set('title', $controller->getPageTitle());
        $view_bag->set('tree', $WT_TREE);
        $view_bag->set('form_url', 'module.php?mod='.$this->module->getName().'&mod_action=SosaConfig&ged='.$WT_TREE->getNameUrl());
        
        $users_root = array();
        $users_js_array = 'var users_array = [];';
        if(Auth::check()) {
            $root_id = $WT_TREE->getUserPreference(Auth::user(), 'MAJ_SOSA_ROOT_ID');
            $users_root[] = array( 'user' => Auth::user(), 'rootid' => $root_id);
            $users_js_array .=  'users_array["'.Auth::user()->getUserId().'"] = "' . $root_id . '";';
            
            if(Auth::isManager($WT_TREE)) {
                $default_user = User::find(-1);
                $default_root_id = $WT_TREE->getUserPreference($default_user, 'MAJ_SOSA_ROOT_ID');
                $users_root[] = array( 'user' => $default_user, 'rootid' => $default_root_id);
                $users_js_array .=  'users_array["'.$default_user->getUserId().'"] = "' . $default_root_id . '";';
            }
        }
        $view_bag->set('users_settings', $users_root);       
        
        $controller->addInlineJavascript($users_js_array . '            
                $("#maj-sosa-config-select").change(function() {
                    $("#rootid").val(users_array[this.value]);
                });
             ');
        
        ViewFactory::make('SosaConfig', $this, $controller, $view_bag)->render();   
    }
    
    /**
     * SosaConfig@computeAll
     */
    public function computeAll() {
        global $WT_TREE;
        
        $controller = new AjaxController();
        $controller->restrictAccess($this->canUpdate());
        
        $view_bag = new ViewBag();
        $view_bag->set('is_success', false);
        
        $user = User::find(Filter::getInteger('userid', -1));
        if($user) {
            $calculator = new SosaCalculator($WT_TREE, $user);
            if($calculator->computeAll()) $view_bag->set('is_success', true);
        }
        ViewFactory::make('SosaComputeResult', $this, $controller, $view_bag)->render();
    }
    
    /**
     * SosaConfig@computePartial
     */
    public function computePartial() {
        global $WT_TREE;
    
        $controller = new AjaxController();
        $controller->restrictAccess($this->canUpdate());
    
        $view_bag = new ViewBag();
        $view_bag->set('is_success', false);
    
        $user = User::find(Filter::getInteger('userid', -1));
        $indi = Individual::getInstance(Filter::get('pid', WT_REGEX_XREF), $WT_TREE);
        
        if($user && $indi) {
            $calculator = new SosaCalculator($WT_TREE, $user);
            if($calculator->computeFromIndividual($indi)) $view_bag->set('is_success', true);
        }
        else {
            $view_bag->set('error', I18N::translate('Non existing individual'));
        }
            
        ViewFactory::make('SosaComputeResult', $this, $controller, $view_bag)->render();
    }
    
}