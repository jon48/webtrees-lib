<?php

 /**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Hooks
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2011-2021, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Contracts\Hooks;

use Fisharebest\Webtrees\Tree;

/**
 * Interface for hooks intending to extend the title of source citations
 */
interface FactSourceTextExtenderInterface extends HookInterface
{
    /**
     * Insert some content before the source citation title.
     *
     * @param Tree $tree
     * @param string $source_record
     * @return string
     */
    public function factSourcePrepend(Tree $tree, string $source_record, int $level): string;

    /**
     * Insert some content after the source citation title.
     *
     * @param Tree $tree
     * @param string $source_record
     * @return string
     */
    public function factSourceAppend(Tree $tree, string $source_record, int $level): string;
}
