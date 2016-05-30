<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Mvc
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Mvc;

use \Fisharebest\Webtrees as fw;
use MyArtJaub\Webtrees\Mvc\Controller\MvcController;

/**
 * Standard concrete implementation of DispatcherInferface
 * @see \MyArtJaub\Webtrees\Mvc\DispatcherInterface
 */
class Dispatcher implements DispatcherInterface {
    
    /**
     * @var Dispatcher $instance Singleton pattern instance
     */
    private static $instance = null;
    
    /**
     * Returns the *Dispatcher* instance of this class.
     *
     * @return Dispatcher The *Singleton* instance.
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
    protected function __construct() {}
    
    /**
     * {@inheritdoc }
     * @see \MyArtJaub\Webtrees\Mvc\DispatcherInterface::handle()
     */
    public function handle(fw\Module\AbstractModule $module, $request) {
		
		$fq_modclass_name = get_class($module);
		$ctrl_namespace = substr($fq_modclass_name, 0, - strlen('Module')) . '\\';
		
		$args = explode( '@', $request, 2);
		switch(count($args)) {
			case 1:
				$ctrl_name = $args[0];
				$method = 'index';
				break;
			case 2:
				list($ctrl_name, $method) = $args;
				break;
			default:
				break;
		}
		
		$ctrl_class = $ctrl_namespace . $ctrl_name . 'Controller';
		if(class_exists($ctrl_class) 
		    && is_subclass_of($ctrl_class, '\\MyArtJaub\\Webtrees\\Mvc\\Controller\\MvcController')
			&& $ctrl = new $ctrl_class($module) ) {
			if(method_exists($ctrl, $method)) {
				call_user_func_array(array($ctrl, $method), array());
			}
			 else {
				 throw new \Exception('The page requested does not exist');
			 }
		 }
		 else {
			 throw new \Exception('The page requested does not exist');
		 }
    }

    /**
     * Private clone method to prevent cloning of the instance of the
     * *Dispatcher* instance.
     *
     * @return void
     */
    private function __clone()
    {
    }
    
    /**
     * Private unserialize method to prevent unserializing of the *Dispatcher*
     * instance.
     *
     * @return void
     */
    private function __wakeup()
    {
    }
    
}

 