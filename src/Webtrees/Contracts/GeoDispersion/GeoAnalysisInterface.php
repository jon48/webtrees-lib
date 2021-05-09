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

namespace MyArtJaub\Webtrees\Contracts\GeoDispersion;

use Fisharebest\Webtrees\Tree;
use MyArtJaub\Webtrees\Common\GeoDispersion\GeoAnalysis\GeoAnalysisResults;

/**
 * Interface for Geographical dispersion analysis.
 */
interface GeoAnalysisInterface
{
    /**
     * Get the geographical dispersion analysis title
     *
     * @return string
     */
    public function title(): string;

    /**
     * Gets the function to translate
     *
     * @return callable(int $count): string
     */
    public function itemsDescription(): callable;

    /**
     * Get the results of the geographical dispersion analysis
     *
     * @param Tree $tree
     * @param int $depth
     * @return GeoAnalysisResults
     */
    public function results(Tree $tree, int $depth): GeoAnalysisResults;
}
