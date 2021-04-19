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

/**
 * Trait for Place Mappers filtering on a defined list of higher level places.
 */
trait TopFilteredPlaceMapperTrait
{
    /**
     * @var Place[] $top_places
     */
    private array $top_places = [];

    /**
     * Get the list of top level places.
     *
     * @return Place[]
     */
    public function topPlaces(): array
    {
        return $this->top_places;
    }

    /**
     * Set the list of defined top level places.
     *
     * @param array $top_places
     */
    public function setTopPlaces(array $top_places): void
    {
        $this->top_places = collect($top_places)
            ->filter(
                /** @psalm-suppress MissingClosureParamType */
                fn($top_places) => $top_places instanceof Place
            )->toArray();
    }

    /**
     * Check whether a string ($haystack) ends with another string ($needle)
     *
     * {@internal This is redundant with the function str_ends_with in PHP8}
     *
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    private function endsWith(string $haystack, string $needle): bool
    {
        return substr_compare($haystack, $needle, -strlen($needle)) === 0;
    }

    /**
     * Check whether a Place belongs to one of the defined top places.
     *
     * @param Place $place
     * @return bool
     */
    protected function belongsToTopLevels(Place $place): bool
    {
        foreach ($this->top_places as $top_place) {
            if (
                $top_place->tree()->id() === $place->tree()->id() &&
                $this->endsWith($place->gedcomName(), $top_place->gedcomName())
            ) {
                    return true;
            }
        }
        return false;
    }
}
