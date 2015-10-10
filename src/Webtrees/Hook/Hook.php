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
		if(HookProvider::isModuleOperational()){
			$statement = fw\Database::prepare(
					"INSERT IGNORE INTO `##phooks` (ph_hook_function, ph_hook_context, ph_module_name)".
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
		if(HookProvider::isModuleOperational()){
			fw\Database::prepare(
			"UPDATE `##phooks`".
			" SET ph_module_priority=?".
			" WHERE ph_hook_function=?".
			" AND ph_hook_context=?".
			" AND ph_module_name=?"
					)->execute(array($priority, $this->hook_function, $this->hook_context, $hsubscriber));
		}
	}

	/**
	 * Enable the hook for a specific HookSubscriberInterface.
	 *
	 * @param string $hsubscriber Name of the subscriber module
	 */
	public function enable($hsubscriber){
		if(HookProvider::isModuleOperational()){
		fw\Database::prepare(
			"UPDATE `##phooks`".
			" SET ph_status='enabled'".
			" WHERE ph_hook_function=?".
			" AND ph_hook_context=?".
			" AND ph_module_name=?"
			)->execute(array($this->hook_function, $this->hook_context, $hsubscriber));
		}
	}

	/**
	 * Disable the hook for a specific HookSubscriberInterface.
	 *
	 * @param string $hsubscriber Name of the subscriber module
	 */
	public function disable($hsubscriber){
		if(HookProvider::isModuleOperational()){
		fw\Database::prepare(
			"UPDATE `##phooks`".
			" SET ph_status='disabled'".
			" WHERE ph_hook_function=?".
			" AND ph_hook_context=?".
			" AND ph_module_name=?"
			)->execute(array($this->hook_function, $this->hook_context, $hsubscriber));
		}
	}

	/**
	 * Remove the hook for a specific HookSubscriberInterface.
	 *
	 * @param string $hsubscriber Name of the subscriber module
	 */
	public function remove($hsubscriber){
		if(HookProvider::isModuleOperational()){
		fw\Database::prepare(
			"DELETE FROM `##phooks`".
			" WHERE ph_hook_function=?".
			" AND ph_hook_context=?".
			" AND ph_module_name=?"
				)->execute(array($this->hook_function, $this->hook_context, $hsubscriber));
		}
	}


	/**
	 * Methods for execution of the Hook
	 *
	 */

	/**
	 * Return the results of the execution of the hook function for all subscribed and enabled modules, in the order defined by their priority.
	 * Parameters can be passed if the hook requires them.
	 *
	 * @return array Results of the hook executions
	 */
	public function execute(){
		$result = array();
		if(HookProvider::isModuleOperational()){
			$params = func_get_args();
			$sqlquery = '';
			$sqlparams = array($this->hook_function);
			if($this->hook_context != 'all') {
				$sqlparams = array($this->hook_function, $this->hook_context);
				$sqlquery = " OR ph_hook_context=?";
			}
			$module_names=fw\Database::prepare(
					"SELECT ph_module_name AS module, ph_module_priority AS priority FROM `##phooks`".
					" WHERE ph_hook_function = ? AND (ph_hook_context='all'".$sqlquery.") AND ph_status='enabled'".
					" ORDER BY ph_module_priority ASC, module ASC"
			)->execute($sqlparams)->fetchAssoc();
			asort($module_names);
			foreach ($module_names as $module_name => $module_priority) {
				require_once WT_ROOT.WT_MODULES_DIR.$module_name.'/module.php';
				$class=$module_name.'_WT_Module';
				$hook_class=new $class();
				$result[] = call_user_func_array(array($hook_class, $this->hook_function), $params);
			}
		}
		return $result;
	}

	/**
	 * Returns the number of active modules linked to a hook
	 *
	 * @return int Number of active modules
	 */
	public function getNumberActiveModules(){
		if(HookProvider::isModuleOperational()){
			$sqlquery = '';
			$sqlparams = array($this->hook_function);
			if($this->hook_context != 'all') {
				$sqlparams = array($this->hook_function, $this->hook_context);
				$sqlquery = " OR ph_hook_context=?";
			}
			$module_names=fw\Database::prepare(
					"SELECT ph_module_name AS modules FROM `##phooks`".
					" WHERE ph_hook_function = ? AND (ph_hook_context='all'".$sqlquery.") AND ph_status='enabled'"
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
