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
 * Interface for hooks intending to add editing capability for a simple tag.
 */
interface CustomSimpleTagEditorInterface extends HookInterface
{
    /**
     * Add the tag in the hierarchy of the expected tags
     *
     * @param array<string, mixed> $expected_tags
     * @return array<string, mixed>
     */
    public function addExpectedTags(array $expected_tags): array;

    /**
     * Try to get a label for a tag
     *
     * @param string $tag
     * @return string
     */
    public function getLabel(string $tag): string;

    /**
     * Returns HTML code for editing the custom tag.
     *
     * @param string $tag
     * @param string $id
     * @param string $name
     * @param string $value
     * @param Tree $tree
     * @return string
     */
    public function editForm(string $tag, string $id, string $name, string $value, Tree $tree): string;
}
