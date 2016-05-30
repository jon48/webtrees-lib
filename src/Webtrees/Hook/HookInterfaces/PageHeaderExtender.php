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

/**
 * Interface for modules which intends to extend the header of the page
 */
interface PageHeaderExtender {
		
    /**
     * Get HTML code for extending the header of a page.
     * 
     * @return string HTML code extension
     */
    public function hPrintHeader();
	
}

?>