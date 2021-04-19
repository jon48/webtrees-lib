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
 * Extension of the Simple Place Mapper, allowing to filter on a defined list of higher level places.
 * Depending on the map, this can help mitigate potential duplicates.
 *
 * @see \MyArtJaub\Webtrees\Module\GeoDispersion\PlaceMappers\SimplePlaceMapper
 */
class SimpleTopFilteredPlaceMapper extends SimplePlaceMapper implements PlaceMapperInterface
{
    use TopFilteredPlaceMapperTrait;

    public function boot(): void
    {
        parent::boot();
        $top_places = $this->config()->get('topPlaces');
        if (is_array($top_places)) {
            $this->setTopPlaces($top_places);
        }
    }

    public function map(Place $place, string $feature_property): ?string
    {
        if (!$this->belongsToTopLevels($place)) {
            return null;
        }
        return parent::map($place, $feature_property);
    }
}
