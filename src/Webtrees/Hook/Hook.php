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

use Fisharebest\Webtrees\Database;
use Fisharebest\Webtrees\Module;

/**
 * Class to manage Hooks (subscription and execution).
 * Accessing list of hooks should be done through a MyArtJaub\Webtrees\Hook\HookSubscriberInterface
 */
class Hook {

	/** @var string Function executed by the hook */
	protected $hook_function;
	
	/** @var string Context in which the hook is executed */
	protected $hook_context;

	/**
	 * Constructor for Hook class
	 *
	 * @param string $hook_function_in Hook function to be subscribed or executed
	 * @param string $hook_context_in Hook context to be subscribed or executed
	 */
	public function __construct($hook_function_in, $hook_context_in = 'all'){
		$this->hook_function = $hook_function_in;
		$this->hook_context = $hook_context_in;
	}

	/**
	 * Methods for subscribing to Hooks
	 */


	/**
	 * Subscribe a class implementing HookSubscriberInterface to the Hook
	 * The Hook is by default enabled.
	 *
	 * @param string $hsubscriber Name of the subscriber module
	 */
	public function subscribe($hsubscriber){
		if(HookProvider::getInstance()->isModuleOperational()){
			Database::prepare(
					"INSERT IGNORE INTO `##maj_hooks` (majh_hook_function, majh_hook_context, majh_module_name)".
					" VALUES (?, ?, ?)"
			)->execute(array($this->hook_function, $this->hook_context, $hsubscriber));
		}
	}

	/**
	 *  Define the priority for execution of the Hook for the specific HookSubscriberInterface
	 *
	 * @param string $hsubscriber Name of the subscriber module
	 * @param int $priority Priority of execution
	 */
	public function setPriority($hsubscriber, $priority){
		if(HookProvider::getInstance()->isModuleOperational()){
			Database::prepare(
			"UPDATE `##maj_hooks`".
			" SET majh_module_priority=?".
			" WHERE majh_hook_function=?".
			" AND majh_hook_context=?".
			" AND majh_module_name=?"
					)->execute(array($priority, $this->hook_function, $this->hook_context, $hsubscriber));
		}
	}

	/**
	 * Enable the hook for a specific HookSubscriberInterface.
	 *
	 * @param string $hsubscriber Name of the subscriber module
	 */
	public function enable($hsubscriber){
		if(HookProvider::getInstance()->isModuleOperational()){
		Database::prepare(
			"UPDATE `##maj_hooks`".
			" SET majh_status='enabled'".
			" WHERE majh_hook_function=?".
			" AND majh_hook_context=?".
			" AND majh_module_name=?"
			)->execute(array($this->hook_function, $this->hook_context, $hsubscriber));
		}
	}

	/**
	 * Disable the hook for a specific HookSubscriberInterface.
	 *
	 * @param string $hsubscriber Name of the subscriber module
	 */
	public function disable($hsubscriber){
		if(HookProvider::getInstance()->isModuleOperational()){
		Database::prepare(
			"UPDATE `##maj_hooks`".
			" SET majh_status='disabled'".
			" WHERE majh_hook_function=?".
			" AND majh_hook_context=?".
			" AND majh_module_name=?"
			)->execute(array($this->hook_function, $this->hook_context, $hsubscriber));
		}
	}

	/**
	 * Remove the hook for a specific HookSubscriberInterface.
	 *
	 * @param string $hsubscriber Name of the subscriber module
	 */
	public function remove($hsubscriber){
		if(HookProvider::getInstance()->isModuleOperational()){
		Database::prepare(
			"DELETE FROM `##maj_hooks`".
			" WHERE majh_hook_function=?".
			" AND majh_hook_context=?".
			" AND majh_module_name=?"
				)->execute(array($this->hook_function, $this->hook_context, $hsubscriber));
		}
	}


	/**
	 * Methods for execution of the Hook
	 *
	 */
	
	/**
	 * Return the resuls of the execution of the hook function for a defined list of modules, only for those enabled.
	 * 
	 * @param string[] $module_names List of Modules to execute
	 * @return array Results of the hook executions
	 */
	public function executeOnlyFor(array $module_names) {
	    $result = array();
	    if(HookProvider::getInstance()->isModuleOperational()){
	       $params = func_get_args();
	       array_shift($params);
    	    foreach ($module_names as $module_name) {
    	        if($module = Module::getModuleByName($module_name)) {
    	            $result[] = call_user_func_array(array($module, $this->hook_function), $params);
    	        }
    	    }
	    }
	    return $result;
	}

	/**
	 * Return the results of the execution of the hook function for all subscribed and enabled modules, in the order defined by their priority.
	 * Parameters can be passed if the hook requires them.
	 *
	 * @return array Results of the hook executions
	 */
	public function execute(){
		$result = array();
		if(HookProvider::getInstance()->isModuleOperational()){
			$params = func_get_args();
			$sqlquery = '';
			$sqlparams = array($this->hook_function);
			if($this->hook_context != 'all') {
				$sqlparams = array($this->hook_function, $this->hook_context);
				$sqlquery = " OR majh_hook_context=?";
			}
			$module_names=Database::prepare(
					"SELECT majh_module_name AS module, majh_module_priority AS priority FROM `##maj_hooks`".
					" WHERE majh_hook_function = ? AND (majh_hook_context='all'".$sqlquery.") AND majh_status='enabled'".
					" ORDER BY majh_module_priority ASC, module ASC"
			)->execute($sqlparams)->fetchAssoc();
			asort($module_names);
			array_unshift($params, array_keys($module_names));
			$result = call_user_func_array(array(&$this, 'executeOnlyFor'), $params);
		}
		return $result;
	}

	/**
	 * Returns the number of active modules linked to a hook
	 *
	 * @return int Number of active modules
	 */
	public function getNumberActiveModules(){
		if(HookProvider::getInstance()->isModuleOperational()){
			$sqlquery = '';
			$sqlparams = array($this->hook_function);
			if($this->hook_context != 'all') {
				$sqlparams = array($this->hook_function, $this->hook_context);
				$sqlquery = " OR majh_hook_context=?";
			}
			$module_names=Database::prepare(
					"SELECT majh_module_name AS modules FROM `##maj_hooks`".
					" WHERE majh_hook_function = ? AND (majh_hook_context='all'".$sqlquery.") AND majh_status='enabled'"
			)->execute($sqlparams)->fetchOneColumn();
			return count($module_names);
		}
		return 0;
	}

	/**
	 * Return whether any active module is linked to a hook
	 *
	 * @return bool True is active modules exist, false otherwise
	 */
	public function hasAnyActiveModule(){
		return ($this->getNumberActiveModules()>0);
	}

}
