<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage {subpackage}
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Mvc\View;

use Fisharebest\Webtrees\Controller\BaseController;
use MyArtJaub\Webtrees\Mvc\Controller\MvcController;
/**
 * Factory to generate views for compatible modules
 */
class ViewFactory {
    
    /**
     * @var ViewFactory $instance Singleton pattern instance
     */
    private static $instance = null;
    
    /**
     * Returns the *ViewFactory* instance of this class.
     *
     * @return ViewFactory The *Singleton* instance.
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }
    
        return static::$instance;
    }
    
    /**
     * Protected constructor
     */
    protected function __construct() {}
              
    /**
     * Return the view specified by the controller and view name, using data from the ViewBag
     * 
     * @param string $view_name
     * @param \MyArtJaub\Webtrees\Mvc\Controller\MvcControllerInterface $mvc_ctrl
     * @param \Fisharebest\Webtrees\Controller\BaseController $ctrl
     * @param \MyArtJaub\Webtrees\Mvc\View\ViewBag $data
     * @return \MyArtJaub\Webtrees\Mvc\View\AbstractView View
     * @throws \Exception
     */
    public function makeView($view_name, MvcController $mvc_ctrl, BaseController $ctrl, ViewBag $data) 
    {
        if(!$mvc_ctrl) throw new \Exception('Mvc Controller not defined');
        if(!$ctrl) throw new \Exception('Base Controller not defined');
        if(!$view_name) throw new \Exception('View not defined');
        
        $mvc_ctrl_refl = new \ReflectionObject($mvc_ctrl);
        $view_class = $mvc_ctrl_refl->getNamespaceName() . '\\Views\\' . $view_name . 'View';       
        if(!class_exists($view_class)) throw new \Exception('View does not exist');
        
        return new $view_class($ctrl, $data);
    }
    
    /**
     * Static invocation of the makeView method
     * 
     * @param string $view_name
     * @param \MyArtJaub\Webtrees\Mvc\Controller\MvcControllerInterface $mvc_ctrl
     * @param \Fisharebest\Webtrees\Controller\BaseController $ctrl
     * @param \MyArtJaub\Webtrees\Mvc\View\ViewBag $data
     * @return \MyArtJaub\Webtrees\Mvc\View\AbstractView View
     * @see \MyArtJaub\Webtrees\Mvc\View\ViewFactory::handle()
     */
    public static function make($view_name, MvcController $mvc_ctrl, BaseController $ctrl, ViewBag $data) 
    {
        return self::getInstance()->makeView($view_name, $mvc_ctrl, $ctrl, $data);
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
 