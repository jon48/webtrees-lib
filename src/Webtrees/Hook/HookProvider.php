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
		if ($hooks===null) {
			$modules = $module_names=fw\Database::prepare("SELECT module_name FROM `##module`")->execute()->fetchOneColumn();
			$dir=opendir(WT_ROOT.WT_MODULES_DIR);
			while (($file=readdir($dir))!==false) {
				if (preg_match('/^[a-zA-Z0-9_]+$/', $file) && file_exists(WT_ROOT.WT_MODULES_DIR.$file.'/module.php')) {
					require_once WT_ROOT.WT_MODULES_DIR.$file.'/module.php';
					$class=$file.'_WT_Module';
					$hook_class=new $class();
					if( in_array($file, $modules) && $hook_class instanceof HookSubscriberInterface){
						$subscribedhooks = $hook_class->getSubscribedHooks();
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
									$hok_cont = $hook_item[1];
								}
								else{
									$hook_func = $hook_item[0];
									$hook_cont = 'all';
								}
								if(method_exists($hook_class, $hook_func)){
									$hooks[$hook_class->getName().'#'.$hook_func.'#'.$hook_cont]=$priority;
								}
							}
						}
					}
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
					"SELECT ph_id AS id, ph_module_name AS module, ph_hook_function AS hook, ph_hook_context as context, ph_module_priority AS priority,  ph_status AS status".
					" FROM `##phooks`".
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
	
}