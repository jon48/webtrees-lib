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

namespace MyArtJaub\Webtrees\Module\GeoDispersion\Model;

use Illuminate\Support\Collection;
use MyArtJaub\Webtrees\Common\GeoDispersion\GeoAnalysis\GeoAnalysisPlace;

/**
 * Data structure for populating map features with their mapped places and count.
 */
class MapFeatureAnalysisData
{
    private int $count;
    private bool $in_map;
    /**
     * @var Collection<GeoAnalysisPlace> $places
     */
    private Collection $places;

    /**
     * Constructor for MapFeatureAnalysisData
     */
    public function __construct()
    {
        $this->count = 0;
        $this->places = new Collection();
        $this->in_map = false;
    }

    /**
     * Get the list of places mapped to the feature
     *
     * @return Collection<GeoAnalysisPlace>
     */
    public function places(): Collection
    {
        return $this->places;
    }

    /**
     * Get the count of analysis items occurring in the feature
     *
     * @return int
     */
    public function count(): int
    {
        return $this->count;
    }

    /**
     * Check whether the feature exist in the target map
     *
     * @return bool
     */
    public function existsInMap(): bool
    {
        return $this->in_map;
    }

    /**
     * Confirm that the feature exist in the target map
     *
     * @return $this
     */
    public function tagAsExisting(): self
    {
        $this->in_map = true;
        return $this;
    }

    /**
     * Add a GeoAnalysisPlace to the feature
     *
     * @param GeoAnalysisPlace $place
     * @param int $count
     * @return $this
     */
    public function add(GeoAnalysisPlace $place, int $count): self
    {
        if (!$place->isExcluded()) {
            $this->places->add($place);
            $this->count += $count;
        }
        return $this;
    }
}
