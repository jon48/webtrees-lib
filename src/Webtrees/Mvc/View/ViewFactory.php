<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage {subpackage}
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) {beging_year}-2015, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Mvc\View;

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
     * @param \Fisharebest\Webtrees\Controller\BaseController $ctrl
     * @param \MyArtJaub\Webtrees\Mvc\View\ViewBag $data
     * @return \MyArtJaub\Webtrees\Mvc\View\AbstractView View
     * @throws \Exception
     */
    public function makeView($view_name, \Fisharebest\Webtrees\Controller\BaseController $ctrl, ViewBag $data) {
        if(!$ctrl) throw new \Exception('Controller not defined');
        if(!$view_name) throw new \Exception('View not defined');
        
        $ctrl_refl = new \ReflectionObject($ctrl);
        $view_class = $ctrl_refl->getNamespaceName() . '\\Views\\' . $view_name . 'View';       
        if(!class_exists($view_class)) throw new \Exception('View does not exist');
        
        return new $view_class($ctrl, $data);
    }
    
    /**
     * Static invocation of the makeView method
     * 
     * @param string $view_name
     * @param \Fisharebest\Webtrees\Controller\BaseController $ctrl
     * @param \MyArtJaub\Webtrees\Mvc\View\ViewBag $data
     * @return \MyArtJaub\Webtrees\Mvc\View\AbstractView View
     * @see \MyArtJaub\Webtrees\Mvc\View\ViewFactory::handle()
     */
    public static function make($view_name, \Fisharebest\Webtrees\Controller\BaseController $ctrl, ViewBag $data) {
        return self::getInstance()->makeView($view_name, $ctrl, $data);
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
 