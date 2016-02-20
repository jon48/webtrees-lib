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

/**
 * MvcControllerInterface
 */
interface MvcControllerInterface {
    
    /**
     * Return the module attached to this controller.
     * 
     * @return \Fisharebest\Webtrees\Module\AbstractModule
     */
    function getModule();
    
}
 