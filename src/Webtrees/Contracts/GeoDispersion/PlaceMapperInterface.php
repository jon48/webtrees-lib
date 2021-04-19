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

use Fisharebest\Webtrees\Place;

/**
 * Interface for Place mappers.
 * Places mappers provide a mapping between a Place and its representation in a GeoJson map
 */
interface PlaceMapperInterface
{   
    /**
     * Boot the Place mapper
     */
    function boot(): void;
    
    /**
     * Get the configuration associated to the mapper
     * 
     * @return PlaceMapperConfigInterface
     */
    function config(): PlaceMapperConfigInterface;
    
    /**
     * Set the configured associated to the mapper
     * 
     * @param PlaceMapperConfigInterface $config
     */
    function setConfig(PlaceMapperConfigInterface $config): void;
    
    /**
     * Get the data associated to the mapper, for a specific key
     * 
     * @param string $key
     * @return null|mixed
     */
    function data(string $key);
    
    /**
     * Set the data associated to the mapper, for a specific key
     * 
     * @param string $key
     * @param mixed $data
     */
    function setData(string $key, $data): void;
    
    /**
     * Return the property value of the feature identifying a place in a GeoJson map.
     * 
     * @param Place $place
     * @param string $feature_property
     * @return string|NULL
     */
    function map(Place $place, string $feature_property) : ?string;
}