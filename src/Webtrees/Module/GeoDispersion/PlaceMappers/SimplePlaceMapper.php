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

namespace MyArtJaub\Webtrees\Module\GeoDispersion\PlaceMappers;

use Fisharebest\Webtrees\Place;
use MyArtJaub\Webtrees\Contracts\GeoDispersion\PlaceMapperInterface;

/**
 * Simple Place Mapper, returning the lowest level place name.
 * Depending on the map, can be a very quick mapper, but no handling of duplicates or place name changes.
 */
class SimplePlaceMapper implements PlaceMapperInterface
{
    use PlaceMapperTrait;

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\GeoDispersion\PlaceMapperInterface::map()
     */
    public function map(Place $place, string $feature_property): ?string
    {
        return $place->firstParts(1)->first();
    }
}
