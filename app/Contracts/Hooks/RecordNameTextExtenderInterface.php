<?php

 /**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Hooks
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2011-2023, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Contracts\Hooks;

use Fisharebest\Webtrees\GedcomRecord;

/**
 * Interface for hooks intending to extend the display name of Gedcom Records
 */
interface RecordNameTextExtenderInterface extends HookInterface
{
    /**
     * Insert some content before the record name text.
     *
     * @param GedcomRecord $record Gedcom record
     * @param bool $use_long Use the long text extender format
     * @param string $size Prepend size
     * @return string
     */
    public function recordNamePrepend(GedcomRecord $record, bool $use_long = false, string $size = ''): string;

    /**
     * Insert some content after the record name text.
     *
     * @param GedcomRecord $record Gedcom record
     * @param bool $use_long Use the long text extender format
     * @param string $size Append size
     * @return string
     */
    public function recordNameAppend(GedcomRecord $record, bool $use_long = false, string $size = ''): string;
}
