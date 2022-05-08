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
     * @param \Fisharebest\Webtrees\Fact|array<array<\Fisharebest\Webtrees\Contracts\ElementInterface|string>> $fact
     * @return string
     */
    public function factSourcePrepend(Tree $tree, $fact): string;

    /**
     * Insert some content after the source citation title.
     *
     * @param Tree $tree
     * @param \Fisharebest\Webtrees\Fact|array<array<\Fisharebest\Webtrees\Contracts\ElementInterface|string>> $fact
     * @return string
     */
    public function factSourceAppend(Tree $tree, $fact): string;
}
