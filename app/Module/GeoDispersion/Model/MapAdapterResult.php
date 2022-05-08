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

use MyArtJaub\Webtrees\Common\GeoDispersion\GeoAnalysis\GeoAnalysisResult;

/**
 * Result of a GeoAnalysisMapAdapter conversion.
 * It includes the GeoAnalysisResult with places excluded by the mapping,
 * and a list of features to display populated with analysis result properties.
 *
 */
class MapAdapterResult
{
    private GeoAnalysisResult $result;

    /**
     * @var array<int, \Brick\Geo\IO\GeoJSON\Feature> $features
     */
    private array $features;

    /**
     * Constructor for MapAdapterResult
     *
     * @param GeoAnalysisResult $result
     * @param \Brick\Geo\IO\GeoJSON\Feature[] $features
     */
    final public function __construct(GeoAnalysisResult $result, array $features)
    {
        $this->result = $result;
        $this->features = $features;
    }

    /**
     * Get the GeoAnalysisResult after mapping of the places
     *
     * @return GeoAnalysisResult
     */
    public function geoAnalysisResult(): GeoAnalysisResult
    {
        return $this->result;
    }

    /**
     * Get the list of features to display on the map
     *
     * @return array<int, \Brick\Geo\IO\GeoJSON\Feature>
     */
    public function features(): array
    {
        return $this->features;
    }

    /**
     * Merge the current MapAdapter with another.
     * The current object is modified, not the second one.
     *
     * @param MapAdapterResult $other
     * @return static
     */
    public function merge(MapAdapterResult $other): self
    {
        return new static(
            $this->result->merge($other->result),
            [...$this->features, ...$other->features]
        );
    }
}
