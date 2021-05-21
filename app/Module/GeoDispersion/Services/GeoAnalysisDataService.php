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
use Fisharebest\Webtrees\I18N;
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

    /**
     * Returns an example of the place hierarchy, from a place within the GEDCOM file, looking for the deepest
     * hierarchy found. The part order is reversed compared to the normal GEDCOM structure (largest first).
     *
     * {@internal The places are taken only from the individuals and families records.}
     *
     * @param Tree $tree
     * @return array<int, string[]>
     */
    public function placeHierarchyExample(Tree $tree): array
    {
        $query_individuals = DB::table('individuals')
            ->select(['i_gedcom AS g_gedcom'])
            ->where('i_file', '=', $tree->id())
            ->where('i_gedcom', 'like', '%2 PLAC %');

        $query_families = DB::table('families')
            ->select(['f_gedcom AS g_gedcom'])
            ->where('f_file', '=', $tree->id())
            ->where('f_gedcom', 'like', '%2 PLAC %');

        return $query_individuals->unionAll($query_families)
            ->get()->pluck('g_gedcom')
            ->flatMap(static function (string $gedcom): array {
                preg_match_all('/\n2 PLAC (.+)/', $gedcom, $matches);
                return $matches[1] ?? [];
            })
            ->sort(I18N::comparator())->reverse()
            ->mapWithKeys(static function (string $place): array {
                $place_array = array_reverse(array_filter(array_map('trim', explode(",", $place))));
                return [ count($place_array) => $place_array ];
            })
            ->sortKeys()
            ->last();
    }
}
