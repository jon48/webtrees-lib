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

namespace MyArtJaub\Webtrees\Contracts\GeoDispersion;

/**
 * Interface for configuration of a map view.
 */
interface MapViewConfigInterface
{
    /**
     * Get the feature property to be used for mapping the map feature with the analysis results
     *
     * @return string
     */
    public function mapMappingProperty(): string;

    /**
     * Get the config of the mapper associated with the map view
     *
     * @return PlaceMapperConfigInterface
     */
    public function mapperConfig(): PlaceMapperConfigInterface;

    /**
     * Get a MapViewConfigInterface object with the new properties
     *
     * @param string $mapping_property
     * @param PlaceMapperConfigInterface $mapper_config
     * @return static
     */
    public function with(string $mapping_property, PlaceMapperConfigInterface $mapper_config): self;
}
