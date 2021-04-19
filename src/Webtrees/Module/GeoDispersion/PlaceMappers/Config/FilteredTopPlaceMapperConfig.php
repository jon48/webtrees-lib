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

namespace MyArtJaub\Webtrees\Module\GeoDispersion\PlaceMappers\Config;

use Fisharebest\Webtrees\Place;
use Fisharebest\Webtrees\Services\TreeService;
use MyArtJaub\Webtrees\Common\GeoDispersion\Config\GenericPlaceMapperConfig;
use RuntimeException;

/**
 * Place Mapper configuration for mappers filtering on Top Places.
 */
class FilteredTopPlaceMapperConfig extends GenericPlaceMapperConfig
{
    private TreeService $tree_service;

    /**
     * FilteredTopPlaceMapperConfig
     *
     * @param TreeService $tree_service
     */
    public function __construct(TreeService $tree_service)
    {
        $this->tree_service = $tree_service;
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Common\GeoDispersion\Config\GenericPlaceMapperConfig::jsonSerializeConfig()
     */
    public function jsonSerializeConfig()
    {
        return [
            'topPlaces' => collect($this->get('topPlaces', []))
                ->filter(
                    /** @psalm-suppress MissingClosureParamType */
                    fn($item): bool => $item instanceof Place
                )->map(fn(Place $place): array => [ $place->tree()->id(), $place->gedcomName() ])
                ->toArray()
        ];
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Common\GeoDispersion\Config\GenericPlaceMapperConfig::jsonDeserialize()
     */
    public function jsonDeserialize($config): self
    {
        if (is_string($config)) {
            return $this->jsonDeserialize(json_decode($config));
        }
        if (is_array($config)) {
            $this->setConfig([
                'topPlaces' => collect($config['topPlaces'] ?? [])
                    ->filter(
                        /** @psalm-suppress MissingClosureParamType */
                        fn($item): bool => is_array($item) && count($item) == 2
                    )->map(function (array $item): ?Place {
                        try {
                            return new Place($item[1], $this->tree_service->find($item[0]));
                        } catch (RuntimeException $ex) {
                            return null;
                        }
                    })
                    ->filter()
                    ->toArray()
                ]);
        }
        return $this;
    }
}
