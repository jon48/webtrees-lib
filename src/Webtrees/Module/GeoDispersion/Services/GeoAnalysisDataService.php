<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage GeoDispersion
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2021, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\GeoDispersion\Services;

use Fisharebest\Webtrees\GedcomRecord;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Tree;
use Illuminate\Database\Capsule\Manager as DB;
use Generator;

/**
 * Service for accessing data used by geographical analyses in the GeoDispersion module.
 */
class GeoAnalysisDataService
{
    /**
     * Yields indviduals and family records for a specified tree.
     *
     * @param Tree $tree
     * @return \Generator<\Fisharebest\Webtrees\GedcomRecord>
     */
    public function individualsAndFamilies(Tree $tree): Generator
    {
        yield from DB::table('individuals')
            ->where('i_file', '=', $tree->id())
            ->select(['individuals.*'])
            ->get()
            ->map(Registry::individualFactory()->mapper($tree))
            ->filter(GedcomRecord::accessFilter())
            ->all();

        yield from DB::table('families')
            ->where('f_file', '=', $tree->id())
            ->select(['families.*'])
            ->get()
            ->map(Registry::familyFactory()->mapper($tree))
            ->filter(GedcomRecord::accessFilter())
            ->all();
    }
}
