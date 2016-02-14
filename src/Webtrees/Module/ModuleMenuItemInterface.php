<?php
/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Module
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2015, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Module;

/**
 * Interface for module wishing to add a menu item to a top menu.   
 */
interface ModuleMenuItemInterface
{
    /**
     * Returns a menu item for the module.
     * 
     * @param \Fisharebest\Webtrees\Tree|null $tree
     * @param mixed $reference
     */
    public function getMenu(\Fisharebest\Webtrees\Tree $tree, $reference);
}