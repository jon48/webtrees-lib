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
 * Interface for modules which have dependencies.
 */
interface DependentInterface {

	/**
	 * Returns whether all prerequisites for this module are met.
	 * 
	 * @return bool True if all prerequisites are met, false otherwise
	 */
	public function validatePrerequisites();

}