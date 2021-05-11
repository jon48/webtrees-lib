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
 * Interface for modules providing an extension feature for texts describing Facts sources.
 */
interface FactSourceTextExtenderInterface {

	/**
	 * Insert some content before the fact source text.
	 * 
	 * @param string $srec Source fact record
	 */
	public function hFactSourcePrepend($srec);
	
	/**
	 * Insert some content after the fact source text.
	 * 
	 * @param string $srec Source fact record
	 */
	public function hFactSourceAppend($srec);
	
}

?>