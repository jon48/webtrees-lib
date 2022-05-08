<?php

 /**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Hooks
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2011-2022, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Contracts\Hooks;

/**
 * Interface for hooks intending to extend the columns of sosa families datatables
 */
interface SosaFamilyDatatablesExtenderInterface extends HookInterface
{
    /**
     * Get the columns to be added to sosa families datatables
     *
     * @param iterable<\Fisharebest\Webtrees\Family> $records
     * @return array<string, array<string, array<string, mixed>>>
     */
    public function sosaFamilyColumns(iterable $records): array;
}
