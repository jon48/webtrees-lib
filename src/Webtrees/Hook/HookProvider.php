<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 * 
 * @package MyArtJaub\Webtrees
 * @subpackage Hook
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2011-2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Hook;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Database;
use Fisharebest\Webtrees\Module;
use MyArtJaub\Webtrees\Constants;
use MyArtJaub\Webtrees\Module\ModuleManager;

/**
 * Provider for hooks. 
 * 
 * Provide access to hooks.
 */
class HookProvider implements HookProviderInterface {

	/**
	 * Default priority to be used for hooks without specified priority. 
	 * The default 99 is a low priority.
	 * @var int DEFAULT_PRIORITY
	 */
	const DEFAULT_PRIORITY = 99;

	/**
	 * @var HookProviderInterface $instance Singleton pattern instance
	 */
	protected static $instance = null;
	

	/**
	 * Returns the *HookProvider* instance of this class.
	 *
	 * @return HookProviderInterface The *Singleton* instance.
	 */
	public static function getInstance()
	{
	    if (null === static::$instance) {
	        static::$instance = new static();
	    }
	
	    return static::$instance;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \MyArtJaub\Webtrees\Hook\HookProviderInterface::get()
	 */
	public function get($hook_function, $hook_context = null) {
	    return new Hook($hook_function, $hook_context);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \MyArtJaub\Webtrees\Hook\HookProviderInterface::isModuleOperational()
	 */
	public function isModuleOperational() {
		return ModuleManager::getInstance()->isOperational(Constants::MODULE_MAJ_HOOKS_NAME);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \MyArtJaub\Webtrees\Hook\HookProviderInterface::getPossibleHooks()
	 */
	public function getPossibleHooks() {
		static $hooks=null;
		if ($hooks === null) {
		    $hooks = array();
		    
		    // Cannot use the same logic as the core Module loading,
		    // as this forces a new include of the module.php file.
		    // This causes issue when classes are defined in this file.
		    // Cannot use Module::getActiveModules as well, as this is private.
		    $module_names = Database::prepare(
		        'SELECT SQL_CACHE module_name FROM `##module`'
		    )->fetchOneColumn();
		    
		    foreach($module_names as $module_name) {
		        $module = Module::getModuleByName($module_name);
		        
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
			}
		}
		return $hooks;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \MyArtJaub\Webtrees\Hook\HookProviderInterface::getRawInstalledHooks()
	 */
	public function getRawInstalledHooks(){
		if(self::isModuleOperational()){
			return Database::prepare(
					"SELECT majh_id AS id, majh_module_name AS module, majh_hook_function AS hook, majh_hook_context as context, majh_module_priority AS priority,  majh_status AS status".
					" FROM `##maj_hooks`".
					" ORDER BY hook ASC, status ASC, priority ASC, module ASC"
					)->execute()->fetchAll();
		}
		return array();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \MyArtJaub\Webtrees\Hook\HookProviderInterface::getInstalledHooks()
	 */
	public function getInstalledHooks(){
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
	 * {@inheritDoc}
	 * @see \MyArtJaub\Webtrees\Hook\HookProviderInterface::updateHooks()
	 */
	public function updateHooks() {
	    
	    if(Auth::isAdmin()){
	        $ihooks = self::getInstalledHooks();
	        $phooks = self::getPossibleHooks();
	        	
	        // Insert hooks not existing yet in the DB
	        if($phooks !== null){
	            foreach($phooks as $phook => $priority){
	                $array_hook = explode('#', $phook);
	                if($ihooks === null || !array_key_exists($phook, $ihooks)){
	                    $chook = new Hook($array_hook[1], $array_hook[2]);
	                    $chook->subscribe($array_hook[0]);
	                    $chook->setPriority($array_hook[0], $priority);
	                }
	            }
	        }
	        	
	        //Remove hooks not existing any more in the file system
	        if($ihooks !== null){
	            foreach(array_keys($ihooks) as $ihook){
	                $array_hook = explode('#', $ihook);
	                if($phooks === null || !array_key_exists($ihook, $phooks)){
	                    $chook = new Hook($array_hook[1], $array_hook[2]);
	                    $chook->remove($array_hook[0]);
	                }
	            }
	        }
	    }
	}
	
}