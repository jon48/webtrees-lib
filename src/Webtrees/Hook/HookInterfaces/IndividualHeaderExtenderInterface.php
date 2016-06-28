<?php
 /**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Hook
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2011-2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */
namespace MyArtJaub\Webtrees\Hook\HookInterfaces;

use Fisharebest\Webtrees\Controller\IndividualController;

/**
 * Interface for modules which intends to extend the header of individuals
 */
interface IndividualHeaderExtenderInterface {
		
	/**
	 * Get HTML code for extending the icons in the individual header
	 *
	 * @param IndividualController $ctrlIndi Individual page controller
	 * @return string HTML code extension
	 */
	public function hExtendIndiHeaderIcons(IndividualController $ctrlIndi);
	
	/**
	 * Get HTML code for extending the left part of the individual header
	 *
	 * @param IndividualController $ctrlIndi Individual page controller
	 * @return string HTML code extension
	 */
	public function hExtendIndiHeaderLeft(IndividualController $ctrlIndi);
	
	/**
	 * Get HTML code for extending the right part of the individual header
	 *
	 * @param IndividualController $ctrlIndi Individual page controller
	 * @return string HTML code extension
	 */
	public function hExtendIndiHeaderRight(IndividualController $ctrlIndi);
	
}

?>