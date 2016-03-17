<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 * 
 * @package MyArtJaub\Webtrees
 * @subpackage Hook
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2011-2015, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Hook;

use \Fisharebest\Webtrees as fw;
use \MyArtJaub\Webtrees as mw;
use \MyArtJaub\Webtrees\Constants;
use Fisharebest\Webtrees\Auth;

/**
 * Provider for hooks. 
 * 
 * Provide access to hooks.
 * @todo Singleton Pattern.
 */
class HookProvider implements HookProviderInterface {

	/**
	 * Default priority to be used for hooks without specified priority. 
	 * The default 99 is a low priority.
	 * @var int DEFAULT_PRIORITY
	 */
	const DEFAULT_PRIORITY = 99;
		
	/**
	 * Return an instance of the hook linked to the specifed function / context
	 * 
	 * @param string $hook_function
	 * @param string $hook_context
	 * @return Hook
	 */
	public static function get($hook_function, $hook_context = null) {
	    return new Hook($hook_function, $hook_context);
	}
	
	/**
	 * Return whether the Hook module is active and the table has been created.
	 *
	 * @uses \MyArtJaub\Webtrees\Module\ModuleManager to check if the module is operational
	 * @return bool True if module active and table created, false otherwise
	 */
	public static function isModuleOperational() {
		return mw\Module\ModuleManager::getInstance()->isOperational(mw\Constants::MODULE_MAJ_HOOKS_NAME);
	}
	
	/**
	 * Get the list of possible hooks in the list of modules files.
	 * A hook will be registered:
	 * 		- for all modules already registered in Webtrees
	 * 		- if the module implements HookSubscriberInterface
	 * 		- if the method exist within the module
	 *
	 * @return Array List of possible hooks, with the priority
	 */
	static public function getPossibleHooks() {
		static $hooks=null;
		if ($hooks === null) {
		    $hooks = array();
		    foreach (glob(WT_ROOT . WT_MODULES_DIR . '*/module.php') as $file) {
		        try {
		            $module = include $file;
		            if($module instanceof HookSubscriberInterface){
						$subscribedhooks = $module->getSubscribedHooks();
						if(is_array($subscribedhooks)){
							foreach($subscribedhooks as $key => $value){
								if(is_int($key)) {
									$hook_item = $value;
									$priority = self::DEFAULT_PRIORITY;
								}
								else{
									$hook_item = explode('#', $key, 2);
									$priority = $value;
								}
								if($hook_item && count($hook_item) == 2){
									$hook_func = $hook_item[0];
									$hook_cont = $hook_item[1];
								}
								else{
									$hook_func = $hook_item[0];
									$hook_cont = 'all';
								}
								if(method_exists($module, $hook_func)){
									$hooks[$module->getName().'#'.$hook_func.'#'.$hook_cont]=$priority;
								}
							}
						}
					}
    			} catch (\Exception $ex) {
    				// Old or invalid module?
    			}
			}
		}
		return $hooks;
	}
	
	/**
	 * Get the list of hooks intalled in webtrees, with their id, status and priority.
	 *
	 * @return array List of installed hooks
	 */
	static public function getRawInstalledHooks(){
		if(self::isModuleOperational()){
			return fw\Database::prepare(
					"SELECT majh_id AS id, majh_module_name AS module, majh_hook_function AS hook, majh_hook_context as context, majh_module_priority AS priority,  majh_status AS status".
					" FROM `##maj_hooks`".
					" ORDER BY hook ASC, status ASC, priority ASC, module ASC"
					)->execute()->fetchAll();
		}
		return array();
	}
	
	/**
	 * Get the list of hooks intalled in webtrees, with their id, status and priority.
	 *
	 * @return Array List of installed hooks, with id, status and priority
	 */
	static public function getInstalledHooks(){
		static $installedhooks =null;
		if($installedhooks===null){
			$dbhooks=self::getRawInstalledHooks();
			foreach($dbhooks as $dbhook){
				$installedhooks[($dbhook->module).'#'.($dbhook->hook).'#'.($dbhook->context)] = array('id' => $dbhook->id, 'status' => $dbhook->status, 'priority' => $dbhook->priority);
			}
		}
		return $installedhooks;
	}
	
	/**
	 * Update the list of hooks, identifying missing ones and removed ones.
	 */
	static public function updateHooks() {
	    
	    if(Auth::isAdmin()){
	        $ihooks = self::getInstalledHooks();
	        $phooks = self::getPossibleHooks();
	        	
	        // Insert hooks not existing yet in the DB
	        if($phooks!=null){
	            foreach($phooks as $phook => $priority){
	                $array_hook = explode('#', $phook);
	                if($ihooks==null || !array_key_exists($phook, $ihooks)){
	                    $chook = new Hook($array_hook[1], $array_hook[2]);
	                    $chook->subscribe($array_hook[0]);
	                    $chook->setPriority($array_hook[0], $priority);
	                }
	            }
	        }
	        	
	        //Remove hooks not existing any more in the file system
	        if($ihooks!=null){
	            foreach($ihooks as $ihook => $status){
	                $array_hook = explode('#', $ihook);
	                if($phooks==null || !array_key_exists($ihook, $phooks)){
	                    $chook = new Hook($array_hook[1], $array_hook[2]);
	                    $chook->remove($array_hook[0]);
	                }
	            }
	        }
	    }
	}
	
}