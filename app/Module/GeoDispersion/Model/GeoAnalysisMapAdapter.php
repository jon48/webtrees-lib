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

namespace MyArtJaub\Webtrees\Module\GeoDispersion\Model;

use Brick\Geo\IO\GeoJSON\Feature;
use Fisharebest\Webtrees\I18N;
use Illuminate\Support\Collection;
use MyArtJaub\Webtrees\Common\GeoDispersion\GeoAnalysis\GeoAnalysisPlace;
use MyArtJaub\Webtrees\Common\GeoDispersion\GeoAnalysis\GeoAnalysisResult;
use MyArtJaub\Webtrees\Common\GeoDispersion\GeoAnalysis\GeoAnalysisResultItem;
use MyArtJaub\Webtrees\Contracts\GeoDispersion\MapDefinitionInterface;
use MyArtJaub\Webtrees\Contracts\GeoDispersion\MapViewConfigInterface;
use MyArtJaub\Webtrees\Contracts\GeoDispersion\PlaceMapperInterface;

/**
 * Adapter to convert the results of a geographical dispersion analysis to data usable by a Map View
 */
class GeoAnalysisMapAdapter
{
    private int $id;
    private int $view_id;
    private MapDefinitionInterface $map;
    private PlaceMapperInterface $place_mapper;
    private MapViewConfigInterface $config;

    /**
     * Constructor for GeoAnalysisMapAdapter
     *
     * @param int $id
     * @param MapDefinitionInterface $map
     * @param PlaceMapperInterface $mapper
     * @param MapViewConfigInterface $config
     */
    public function __construct(
        int $id,
        int $view_id,
        MapDefinitionInterface $map,
        PlaceMapperInterface $mapper,
        MapViewConfigInterface $config
    ) {
        $this->id = $id;
        $this->view_id = $view_id;
        $this->map = $map;
        $this->place_mapper = $mapper;
        $this->config = $config;
        $this->place_mapper->setConfig($this->config->mapperConfig());
        $this->place_mapper->setData('map', $map);
        $this->place_mapper->boot();
    }

    /**
     * Create a copy of the GeoAnalysisMapAdapter with new properties.
     *
     * @param MapDefinitionInterface $map
     * @param PlaceMapperInterface $mapper
     * @param string $mapping_property
     * @return static
     */
    public function with(
        MapDefinitionInterface $map,
        PlaceMapperInterface $mapper,
        string $mapping_property
    ): self {
        $new = clone $this;
        $new->map = $map;
        $new->place_mapper = $mapper;
        $new->config = $this->config->with($mapping_property, $mapper->config());
        return $new;
    }

    /**
     * Get the GeoAnalysisMapAdapter ID
     *
     * @return int
     */
    public function id(): int
    {
        return $this->id;
    }

    /**
     * Get the ID of the associated GeoAnalysisView
     *
     * @return int
     */
    public function geoAnalysisViewId(): int
    {
        return $this->view_id;
    }

    /**
     * Get the associated target map
     *
     * @return MapDefinitionInterface
     */
    public function map(): MapDefinitionInterface
    {
        return $this->map;
    }

    /**
     * Get the Place Mapper used for the mapping
     *
     * @return PlaceMapperInterface
     */
    public function placeMapper(): PlaceMapperInterface
    {
        return $this->place_mapper;
    }

    /**
     * Get the configuration of the Map View.
     *
     * @return MapViewConfigInterface
     */
    public function viewConfig(): MapViewConfigInterface
    {
        return $this->config;
    }

    /**
     * Convert the geographical analysis result to a MapAdapter result for usage in the Map View
     *
     * @param GeoAnalysisResult $result
     * @return MapAdapterResult
     */
    public function convert(GeoAnalysisResult $result): MapAdapterResult
    {
        $result = $result->copy();

        $features = [];
        list($features_data, $result) = $this->featureAnalysisData($result);

        $places_found = $result->countFound();
        foreach ($this->map->features() as $feature) {
            $feature_id = $this->featureId($feature);
            if ($feature_id !== null && $features_data->has($feature_id)) {
                /** @var MapFeatureAnalysisData $feature_data */
                $feature_data = $features_data->get($feature_id)->tagAsExisting();
                $place_count = $feature_data->count();
                $features[] = $feature
                    ->withProperty('count', $place_count)
                    ->withProperty('ratio', $places_found > 0 ? $place_count / $places_found : 0)
                    ->withProperty(
                        'places',
                        $feature_data->places()
                            ->map(fn(GeoAnalysisPlace $place): string => $place->place()->firstParts(1)->first())
                            ->sort(I18N::comparator())
                            ->toArray()
                    );
            } else {
                $features[] = $feature;
            }
        }

        $features_data
            ->filter(fn(MapFeatureAnalysisData $data) => !$data->existsInMap())
            ->each(
                fn (MapFeatureAnalysisData $data) =>
                    $data->places()->each(
                        fn(GeoAnalysisPlace $place) => $result->exclude($place)
                    )
            );

        return new MapAdapterResult($result, $features);
    }

    /**
     * Populate the map features with the mapped Places and total count
     *
     * @param GeoAnalysisResult $result
     * @return mixed[]
     */
    protected function featureAnalysisData(GeoAnalysisResult $result): array
    {
        $features_mapping = new Collection();

        $byplaces = $result->knownPlaces();
        $byplaces->each(function (GeoAnalysisResultItem $item) use ($features_mapping, $result): void {
            $id = $this->place_mapper->map($item->place()->place(), $this->config->mapMappingProperty());

            if ($id !== null && mb_strlen($id) > 0) {
                $features_mapping->put(
                    $id,
                    $features_mapping->get($id, new MapFeatureAnalysisData($id))->add($item->place(), $item->count())
                );
            } else {
                $result->exclude($item->place());
            }
        });

        return [ $features_mapping, $result];
    }

    /**
     * Get the value of the feature property used for the mapping
     *
     * @param Feature $feature
     * @return string|NULL
     */
    protected function featureId(Feature $feature): ?string
    {
        return $feature->getProperty($this->config->mapMappingProperty());
    }
}
