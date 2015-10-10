<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Module
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2011-2015, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module;

use \Fisharebest\Webtrees as fw;
use \MyArtJaub\Webtrees as mw;

/**
 * Modules manager
 * 
 * Singleton Pattern.
 * Allow access and management of the modules.
 * 
 * @uses ModuleManagerInterface
 * @todo snake_case
 */
class ModuleManager implements ModuleManagerInterface {

	/**
	 * @var ModuleManager $instance Singleton pattern instance
	 */
	private static $instance = null;
	
	/**
	 * @var array $modules_list List of modules
	 */
	private $modules_list;

	/**
	 * Returns the *ModuleManager* instance of this class.
	 *
	 * @return ModuleManager The *Singleton* instance.
	 */
	public static function getInstance()
	{
		if (null === static::$instance) {
			static::$instance = new static();
		}
	
		return static::$instance;
	}
	
	/**
     * Protected constructor.
     */
    protected function __construct()
    {
    	$this->modules_list = array();
    }
    
    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Module\ModuleManagerInterface::isOperational()
     */
    public function isOperational($moduleName) {
    	if(!array_key_exists($moduleName, $this->modules_list)) {
    		if($module = fw\Module::getModuleByName($moduleName)) {
    			if($module instanceof DependentInterface) {
    				if($module->validatePrerequisites()) {
    					$this->modules_list[$moduleName] = TRUE;
    					return true;
    				} else {
    					// Do not cache the result,
    					// as they could change by the next call to the method
    					return false;
    				}
    			}
    			else {
    				$this->modules_list[$moduleName] = TRUE;
    				return true;
    			}
    		}
    		else {
    			$this->modules_list[$moduleName] = FALSE;
    		}
    	}
    	return $this->modules_list[$moduleName];
    	
    }
    

    /**
     * Private clone method to prevent cloning of the instance of the
     * *ModuleManager* instance.
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Private unserialize method to prevent unserializing of the *ModuleManager*
     * instance.
     *
     * @return void
     */
    private function __wakeup()
    {
    }
    
}
