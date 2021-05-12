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

namespace MyArtJaub\Webtrees\Module\GeoDispersion\Services;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Support\Collection;
use MyArtJaub\Webtrees\Common\GeoDispersion\Config\MapViewConfig;
use MyArtJaub\Webtrees\Contracts\GeoDispersion\PlaceMapperConfigInterface;
use MyArtJaub\Webtrees\Contracts\GeoDispersion\PlaceMapperInterface;
use MyArtJaub\Webtrees\Module\GeoDispersion\Model\GeoAnalysisMapAdapter;
use MyArtJaub\Webtrees\Module\GeoDispersion\Views\AbstractGeoAnalysisView;
use MyArtJaub\Webtrees\Module\GeoDispersion\Views\GeoAnalysisMap;
use Closure;
use stdClass;

/**
 * Service for accessing geographical analysis map adapters.
 */
class MapAdapterDataService
{
    private MapDefinitionsService $mapdefinition_service;

    /**
     * Constructor for MapAdapterDataService
     *
     * @param MapDefinitionsService $mapdefinition_service
     */
    public function __construct(MapDefinitionsService $mapdefinition_service)
    {
        $this->mapdefinition_service = $mapdefinition_service;
    }

    /**
     * Find a GeoAnalysisMapAdapter by ID
     *
     * @param int $id
     * @return GeoAnalysisMapAdapter|NULL
     */
    public function find(int $id): ?GeoAnalysisMapAdapter
    {
        return DB::table('maj_geodisp_mapviews')
            ->select('maj_geodisp_mapviews.*')
            ->where('majgm_id', '=', $id)
            ->get()
            ->map($this->mapAdapterMapper())
            ->first();
    }

    /**
     * Get all GeoAnalysisMapAdapters linked to a Map View.
     *
     * @param GeoAnalysisMap $map_view
     * @param bool $show_invalid
     * @return Collection<GeoAnalysisMapAdapter|null>
     */
    public function allForView(GeoAnalysisMap $map_view, bool $show_invalid = false): Collection
    {
        $map_adapters = DB::table('maj_geodisp_mapviews')
            ->select('maj_geodisp_mapviews.*')
            ->where('majgm_majgv_id', '=', $map_view->id())
            ->get()
            ->map($this->mapAdapterMapper());
        return $show_invalid ? $map_adapters : $map_adapters->filter();
    }

    /**
     * Insert a GeoAnalysisMapAdapter in the database.
     *
     * @param GeoAnalysisMapAdapter $map_adapter
     * @return int
     */
    public function insertGetId(GeoAnalysisMapAdapter $map_adapter): int
    {
        return DB::table('maj_geodisp_mapviews')
            ->insertGetId([
                'majgm_majgv_id' => $map_adapter->geoAnalysisViewId(),
                'majgm_map_id' => $map_adapter->map()->id(),
                'majgm_mapper' => get_class($map_adapter->placeMapper()),
                'majgm_feature_prop' => $map_adapter->viewConfig()->mapMappingProperty(),
                'majgm_config' => json_encode($map_adapter->viewConfig()->mapperConfig())
            ]);
    }

    /**
     * Update a GeoAnalysisMapAdapter in the database.
     *
     * @param GeoAnalysisMapAdapter $map_adapter
     * @return int
     */
    public function update(GeoAnalysisMapAdapter $map_adapter): int
    {
        return DB::table('maj_geodisp_mapviews')
            ->where('majgm_id', '=', $map_adapter->id())
            ->update([
                'majgm_map_id' => $map_adapter->map()->id(),
                'majgm_mapper' => get_class($map_adapter->placeMapper()),
                'majgm_feature_prop' => $map_adapter->viewConfig()->mapMappingProperty(),
                'majgm_config' => json_encode($map_adapter->placeMapper()->config())
            ]);
    }

    /**
     * Delete a GeoAnalysisMapAdapter from the database.
     *
     * @param GeoAnalysisMapAdapter $map_adapter
     * @return int
     */
    public function delete(GeoAnalysisMapAdapter $map_adapter): int
    {
        return DB::table('maj_geodisp_mapviews')
            ->where('majgm_id', '=', $map_adapter->id())
            ->delete();
    }

    /**
     * Delete invalid GeoAnalysisMapAdapters from the database.
     *
     * @param AbstractGeoAnalysisView $view
     * @param Collection $valid_map_adapters
     * @return int
     */
    public function deleteInvalid(AbstractGeoAnalysisView $view, Collection $valid_map_adapters): int
    {
        return DB::table('maj_geodisp_mapviews')
            ->where('majgm_majgv_id', '=', $view->id())
            ->whereNotIn('majgm_id', $valid_map_adapters)
            ->delete();
    }

    /**
     * Get the closure to create a GeoAnalysisMapAdapter object from a row in the database.
     * It returns null if the classes stored in the DB cannot be loaded through the Laravel container,
     * or if the types do not match with the ones expected.
     *
     * @return Closure(\stdClass $row):?GeoAnalysisMapAdapter
     */
    private function mapAdapterMapper(): Closure
    {
        return function (stdClass $row): ?GeoAnalysisMapAdapter {
            if (null === $map = $this->mapdefinition_service->find($row->majgm_map_id)) {
                return null;
            }
            try {
                $mapper = app($row->majgm_mapper);
                if (!($mapper instanceof PlaceMapperInterface)) {
                    return null;
                }

                return new GeoAnalysisMapAdapter(
                    (int) $row->majgm_id,
                    (int) $row->majgm_majgv_id,
                    $map,
                    app($row->majgm_mapper),
                    new MapViewConfig($row->majgm_feature_prop, $this->mapperConfigDecoder($row->majgm_config))
                );
            } catch (BindingResolutionException $ex) {
                return null;
            }
        };
    }

    /**
     * Create a PlaceMapperConfigInterface object from a JSON column value.
     * Returns null if the JSON string is invalid/empty or if the extracted mapper class cannot be loaded
     * through the Laravel container or if the type do not match with the one expected.
     *
     * @param string $json_config
     * @return PlaceMapperConfigInterface|NULL
     */
    private function mapperConfigDecoder(?string $json_config): ?PlaceMapperConfigInterface
    {
        $config = $json_config === null ? [] : json_decode($json_config, true);
        $class = $config['class'] ?? null;
        $json_mapper_config = $config['config'] ?? null;
        if ($class === null || $json_mapper_config === null) {
            return null;
        }
        try {
            $mapper_config = app($class);
            if (!$mapper_config instanceof PlaceMapperConfigInterface) {
                return null;
            }
            return $mapper_config->jsonDeserialize($json_mapper_config);
        } catch (BindingResolutionException $ex) {
            return null;
        }
    }
}
