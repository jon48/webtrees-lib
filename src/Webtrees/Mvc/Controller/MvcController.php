<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Mvc
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) {beging_year}-2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Mvc\Controller;

use Fisharebest\Webtrees\Module\AbstractModule;

/**
 * Controller MVC to handle requests
 */
class MvcController implements MvcControllerInterface 
{
    /**
     * @var Fisharebest\Webtrees\Module\AbstractModule $module
     */
    protected $module;
    
    /**
     * Constructor for MvcController
     * 
     * @param AbstractModule $module
     */
    public function __construct(AbstractModule $module) {
        $this->module = $module;
    }
    
    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Mvc\Controller\MvcControllerInterface::getModule()
     */
    public function getModule() {
        return $this->module;
    }
        
}
 