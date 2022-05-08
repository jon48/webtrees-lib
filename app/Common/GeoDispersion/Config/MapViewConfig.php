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

namespace MyArtJaub\Webtrees\Common\GeoDispersion\Config;

use MyArtJaub\Webtrees\Contracts\GeoDispersion\MapViewConfigInterface;
use MyArtJaub\Webtrees\Contracts\GeoDispersion\PlaceMapperConfigInterface;

/**
 * Generic implementation of the MapViewConfigInterface
 *
 */
class MapViewConfig implements MapViewConfigInterface
{
    private string $map_mapping_property;
    private PlaceMapperConfigInterface $mapper_config;

    /**
     * Constructor for MapViewConfig
     *
     * @param string $map_mapping_property
     * @param PlaceMapperConfigInterface $mapper_config
     */
    public function __construct(
        string $map_mapping_property,
        PlaceMapperConfigInterface $mapper_config = null
    ) {
        $this->map_mapping_property = $map_mapping_property;
        $this->mapper_config = $mapper_config ?? new NullPlaceMapperConfig();
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\GeoDispersion\MapViewConfigInterface::mapMappingProperty()
     */
    public function mapMappingProperty(): string
    {
        return $this->map_mapping_property;
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\GeoDispersion\MapViewConfigInterface::mapperConfig()
     */
    public function mapperConfig(): PlaceMapperConfigInterface
    {
        return $this->mapper_config;
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\GeoDispersion\MapViewConfigInterface::with()
     * @return static
     */
    public function with(string $mapping_property, PlaceMapperConfigInterface $mapper_config): self
    {
        $new = clone $this;
        $new->map_mapping_property = $mapping_property;
        $new->mapper_config = $mapper_config;
        return $new;
    }
}
