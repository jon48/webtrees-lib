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

namespace MyArtJaub\Webtrees\Module\GeoDispersion\PlaceMappers;

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Place;
use MyArtJaub\Webtrees\Contracts\GeoDispersion\PlaceMapperInterface;
use MyArtJaub\Webtrees\Contracts\GeoDispersion\PlaceMapperConfigInterface;
use MyArtJaub\Webtrees\Module\GeoDispersion\PlaceMappers\Config\FilteredTopPlaceMapperConfig;

/**
 * Extension of the Simple Place Mapper, allowing to filter on a defined list of higher level places.
 * Depending on the map, this can help mitigate potential duplicates.
 *
 * @see \MyArtJaub\Webtrees\Module\GeoDispersion\PlaceMappers\SimplePlaceMapper
 */
class SimpleTopFilteredPlaceMapper extends SimplePlaceMapper implements PlaceMapperInterface
{
    use TopFilteredPlaceMapperTrait;

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Module\GeoDispersion\PlaceMappers\SimplePlaceMapper::title()
     */
    public function title(): string
    {
        return I18N::translate('Mapping on place name with filter');
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\GeoDispersion\PlaceMapperInterface::boot()
     */
    public function boot(): void
    {
        parent::boot();
        $top_places = $this->config()->get('topPlaces');
        if (is_array($top_places)) {
            $this->setTopPlaces($top_places);
        }
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\GeoDispersion\PlaceMapperInterface::config()
     */
    public function config(): PlaceMapperConfigInterface
    {
        if (!(parent::config() instanceof FilteredTopPlaceMapperConfig)) {
            $this->setConfig(app(FilteredTopPlaceMapperConfig::class));
        }
        return parent::config();
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Module\GeoDispersion\PlaceMappers\SimplePlaceMapper::map()
     */
    public function map(Place $place, string $feature_property): ?string
    {
        if (!$this->belongsToTopLevels($place)) {
            return null;
        }
        return parent::map($place, $feature_property);
    }
}
