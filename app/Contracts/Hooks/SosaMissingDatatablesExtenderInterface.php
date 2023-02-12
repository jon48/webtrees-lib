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

/**
 * Interface for hooks intending to extend the columns of missing ancestors datatables
 */
interface SosaMissingDatatablesExtenderInterface extends HookInterface
{
    /**
     * Get the columns to be added to missing ancestors datatables
     *
     * @param iterable<\Fisharebest\Webtrees\Individual> $records
     * @return array<string, array<string, array<string, mixed>>>
     */
    public function sosaMissingColumns(iterable $records): array;
}
