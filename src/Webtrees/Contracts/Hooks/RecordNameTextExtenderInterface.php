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

use Fisharebest\Webtrees\GedcomRecord;

/**
 * Interface for modules which intends to extend the display name of Gedcom Records
 */
interface RecordNameTextExtenderInterface {

	/**
	 * Insert some content before the record name text.
	 * 
	 * @param GedcomRecord $grec Gedcom record
	 * @param string $size Prepend size
	 */
	public function hRecordNamePrepend(GedcomRecord $grec, $size);
	
	/**
	 * Insert some content after the record name text.
	 * 
	 * @param GedcomRecord $grec Gedcom record
	 * @param string $size Append size
	 */
	public function hRecordNameAppend(GedcomRecord $grec, $size);
	
}

?>