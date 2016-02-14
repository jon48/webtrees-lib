<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Mvc
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2015-2015, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Mvc\View;

/**
 * Abstract class for MVC Views.
 */
abstract class AbstractView {
    
    /**
     * @var \Fisharebest\Webtrees\Controller\BaseController $data
     */
    protected $ctrl;
    
    /**
     * @var ViewBag $data
     */
    protected $data;
    
    /**
     * Constructor 
     * @param \Fisharebest\Webtrees\Controller\BaseController $ctrl Controller
     * @param ViewBag $data ViewBag holding view data
     */
    public function __construct(\Fisharebest\Webtrees\Controller\BaseController $ctrl, ViewBag $data) {
        $this->ctrl = $ctrl;
        $this->data = $data;
    }
    
    /**
     * Render the view to the page.
     * 
     * @throws \Exception
     */
    public function render() {
		global $controller;
		
        if(!$this->ctrl) throw new \Exception('Controller not initialised');
        
		$controller = $this->ctrl;
        $this->ctrl->pageHeader();
        
        echo $this->renderContent();
    }
    
    /**
     * Abstract method containing the details of the view.
     */
    abstract protected function renderContent();
    
}
 