<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage WelcomeBlock
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2011, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module\Hooks;

use Fisharebest\Webtrees\I18N;
use MyArtJaub\Webtrees\Mvc\View\ViewFactory;
use MyArtJaub\Webtrees\Mvc\View\ViewBag;
use Fisharebest\Webtrees\Controller\PageController;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Theme;
use Fisharebest\Webtrees\Filter;
use MyArtJaub\Webtrees\Mvc\Controller\MvcController;
use MyArtJaub\Webtrees\Hook\HookProvider;
use Rhumsaa\Uuid\Uuid;
use Fisharebest\Webtrees\Theme\AdministrationTheme;
use Fisharebest\Webtrees\Module;
use Fisharebest\Webtrees\Database;
use MyArtJaub\Webtrees\Hook\Hook;

/**
 * Controller for Lineage
 */
class AdminConfigController extends MvcController
{
    /**
     * Manage updates sent from the AdminConfig@index form.
     */
    protected function update() {
        if(Auth::isAdmin()){
            $ihooks = HookProvider::getInstalledHooks();
            	
            $module_names= Database::prepare(
                "SELECT module_name FROM `##module` WHERE status='disabled'"
            )->fetchOneColumn();
            	
            if($ihooks!=null){
                foreach ($ihooks as $ihook => $params) {
                    $array_hook = explode('#', $ihook);
                    //Update status
                    $new_status= Filter::postBool("status-{$params['id']}");
                    if(in_array($array_hook[0], $module_names)) $new_status = false;
                    $previous_status = $params['status'];
                    if ($new_status !== null) {
                        $new_status= $new_status ? 'enabled' : 'disabled';
                        if($new_status != $previous_status){
                            $chook = new Hook($array_hook[1], $array_hook[2]);
                            switch($new_status){
                                case 'enabled':
                                    $chook->enable($array_hook[0]);
                                    break;
                                case 'disabled':
                                    $chook->disable($array_hook[0]);
                                    break;
                                default:
                                    break;
                            }
                        }
                    }
                    
                    //Update priority
                    $new_priority = Filter::postInteger("moduleorder-{$params['id']}");
                    $previous_priority = $params['priority'];
                    if ($new_priority !== null) {
                        if($new_priority != $previous_priority){
                            $chook = new Hook($array_hook[1], $array_hook[2]);
                            $chook->setPriority($array_hook[0], $new_priority);
                        }
                    }
                }
            }
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
        
        HookProvider::updateHooks();
        
        $action = Filter::post('action');        
        if($action == 'update' && Filter::checkCsrf()) $this->update();
        
        Theme::theme(new AdministrationTheme)->init($WT_TREE);        
        $ctrl = new PageController();
        $ctrl
            ->restrictAccess(Auth::isAdmin())
            ->setPageTitle($this->module->getTitle());
        
        $table_id = 'table-installedhooks-' . Uuid::uuid4();

        $view_bag = new ViewBag();
        $view_bag->set('title', $ctrl->getPageTitle());
        $view_bag->set('table_id', $table_id);
        $view_bag->set('hook_list', HookProvider::getRawInstalledHooks());
        
        $ctrl
        ->addExternalJavascript(WT_JQUERY_DATATABLES_JS_URL)
        ->addExternalJavascript(WT_DATATABLES_BOOTSTRAP_JS_URL)
        ->addInlineJavascript('
		  	jQuery(document).ready(function() {
				jQuery("#'.$table_id.'").dataTable( {
					'.I18N::datatablesI18N().',		
					sorting: [[ 2, "asc" ], [ 3, "asc" ]],
					displayLength: 10,
					pagingType: "full_numbers",
					columns: [
						/* 0 Enabled 		*/	{ dataSort: 1, class: "center" },
						/* 1 Enabled sort	*/	{ visible: false},
						/* 2 Hook function	*/	null,
						/* 3 Hook context	*/	null,
						/* 4 Module name	*/	null,
						/* 5 Priority		*/	{ dataSort: 6, class: "center" },
						/* 6 Priority sort	*/	{ type: "num", visible: false}
					]
			  });
			});
		');
        
        ViewFactory::make('AdminConfig', $this, $ctrl, $view_bag)->render();
    }
        
}