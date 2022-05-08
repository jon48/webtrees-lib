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
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\Module\ModuleInterface;
use Fisharebest\Webtrees\Services\TreeService;
use Illuminate\Support\Collection;
use MyArtJaub\Webtrees\Common\GeoDispersion\Config\GenericPlaceMapperConfig;
use Psr\Http\Message\ServerRequestInterface;
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
     * Get the configured Top Places to filter on
     *
     * @return Collection<Place>
     */
    public function topPlaces(): Collection
    {
        return collect($this->get('topPlaces', []))
            ->filter(
                /** @psalm-suppress MissingClosureParamType */
                fn($item): bool => $item instanceof Place
            );
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Common\GeoDispersion\Config\GenericPlaceMapperConfig::jsonSerializeConfig()
     */
    public function jsonSerializeConfig()
    {
        return [
            'topPlaces' => $this->topPlaces()
                ->map(fn(Place $place): array => [ $place->tree()->id(), $place->gedcomName() ])
                ->toArray()
        ];
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Common\GeoDispersion\Config\GenericPlaceMapperConfig::jsonDeserialize()
     *
     * @param mixed $config
     * @return $this
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

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Common\GeoDispersion\Config\GenericPlaceMapperConfig::configContent()
     */
    public function configContent(ModuleInterface $module, Tree $tree): string
    {
        return view($module->name() . '::mappers/filtered-top-config', [
            'tree'          =>  $tree,
            'top_places'    =>  $this->topPlaces()
        ]);
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Common\GeoDispersion\Config\GenericPlaceMapperConfig::withConfigUpdate()
     * @return $this
     */
    public function withConfigUpdate(ServerRequestInterface $request): self
    {
        $tree = Validator::attributes($request)->treeOptional();

        if ($tree === null) {
            return $this;
        }

        $top_places = Validator::parsedBody($request)->array('mapper_filt_top_places');
        $config = ['topPlaces' => []];
        foreach ($top_places as $top_place_id) {
            $place = Place::find((int) $top_place_id, $tree);
            if (mb_strlen($place->gedcomName()) > 0) {
                $config['topPlaces'][] = $place;
            }
        }
        $this->setConfig($config);
        return $this;
    }
}
