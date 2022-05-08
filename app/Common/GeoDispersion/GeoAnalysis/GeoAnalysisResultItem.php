<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage GeoDispersion
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2021-2022, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Common\GeoDispersion\GeoAnalysis;

/**
 * Individual item in the result of a geographical dispersion analysis.
 * It references the GeoAnalysis Place, and its number of occurrences in the analysis.
 */
class GeoAnalysisResultItem
{
    private GeoAnalysisPlace $place;
    private int $count;

    /**
     * Constructor for GeoAnalysisResultItem
     *
     * @param GeoAnalysisPlace $place
     * @param int $count
     */
    public function __construct(GeoAnalysisPlace $place, int $count = 0)
    {
        $this->place = $place;
        $this->count = $count;
    }

    /**
     * Get the item key.
     *
     * @return string
     */
    public function key(): string
    {
        return $this->place->key();
    }

    /**
     * Get the referenced GeoAnalysis Place
     *
     * @return GeoAnalysisPlace
     */
    public function place(): GeoAnalysisPlace
    {
        return $this->place;
    }

    /**
     * Get the count of occurrences of the GeoAnalysis Place in the analysis
     *
     * @return int
     */
    public function count(): int
    {
        return $this->count;
    }

    /**
     * Increment the count of occurrences of the GeoAnalysis Place in the analysis
     *
     * @return $this
     */
    public function increment(): self
    {
        $this->count++;
        return $this;
    }

    /**
     * Clone the item object
     */
    public function __clone()
    {
        $this->place = clone $this->place;
    }
}
