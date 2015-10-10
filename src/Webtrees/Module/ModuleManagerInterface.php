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

/**
 * Interface to be implemented by classes managing modules
 */
interface ModuleManagerInterface {
	
	/**
	 * Returns whether the referenced modules is operational.
	 * 
	 * @param bool $module_name True if the module is operational, false otherwise.
	 */
	public function isOperational($module_name);
	
}