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

use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Tree;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Support\Collection;
use MyArtJaub\Webtrees\Common\GeoDispersion\Config\MapColorsConfig;
use MyArtJaub\Webtrees\Common\GeoDispersion\Config\MapViewConfig;
use MyArtJaub\Webtrees\Contracts\GeoDispersion\GeoAnalysisInterface;
use MyArtJaub\Webtrees\Contracts\GeoDispersion\PlaceMapperConfigInterface;
use MyArtJaub\Webtrees\Contracts\GeoDispersion\PlaceMapperInterface;
use MyArtJaub\Webtrees\Module\GeoDispersion\Model\GeoAnalysisMapAdapter;
use MyArtJaub\Webtrees\Module\GeoDispersion\Views\AbstractGeoAnalysisView;
use MyArtJaub\Webtrees\Module\GeoDispersion\Views\GeoAnalysisMap;
use Spatie\Color\Exceptions\InvalidColorValue;
use Closure;
use stdClass;

/**
 * Service for accessing Geographical dispersion analysis views configuration data.
 */
class GeoAnalysisViewDataService
{
    private MapDefinitionsService $mapdefinition_service;

    /**
     * Constructor for GeoAnalysisViewDataService
     *
     * @param MapDefinitionsService $mapdefinition_service
     */
    public function __construct(MapDefinitionsService $mapdefinition_service)
    {
        $this->mapdefinition_service = $mapdefinition_service;
    }

    /**
     * Find a Geographical dispersion analysis view by ID
     *
     * @param Tree $tree
     * @param int $id
     * @return AbstractGeoAnalysisView|NULL
     */
    public function find(Tree $tree, int $id): ?AbstractGeoAnalysisView
    {
        return $this->all($tree)->first(fn(AbstractGeoAnalysisView $view): bool => $view->id() === $id);
    }

    /**
     * Get all Geographical dispersion analysis views, with or without the disabled ones.
     *
     * {@internal It would ignore any view for which the class could not be loaded by the container}
     *
     * @param Tree $tree
     * @param bool $include_disabled
     * @return Collection<AbstractGeoAnalysisView>
     */
    public function all(Tree $tree, bool $include_disabled = false): Collection
    {
        return Registry::cache()->array()->remember(
            'all-geodispersion-views',
            function () use ($tree, $include_disabled): Collection {
                return DB::table('maj_geodisp_views')
                    ->select('maj_geodisp_views.*')
                    ->where('majgv_gedcom_id', '=', $tree->id())
                    ->get()
                    ->map($this->viewMapper($tree))
                    ->filter()
                    ->filter($this->enabledFilter($include_disabled));
            }
        );
    }

    /**
     * Get all GeoAnalysisMapAdapters linked to a Map View.
     *
     * @param GeoAnalysisMap $map_view
     * @return Collection<GeoAnalysisMapAdapter>
     */
    public function mapAdapters(GeoAnalysisMap $map_view): Collection
    {
        return DB::table('maj_geodisp_mapviews')
            ->select('maj_geodisp_mapviews.*')
            ->where('majgm_majgv_id', '=', $map_view->id())
            ->get()
            ->map($this->mapAdapterMapper())
            ->filter();
    }

    /**
     * Get the closure to create a AbstractGeoAnalysisView object from a row in the database.
     * It returns null if the classes stored in the DB cannot be loaded through the Laravel container,
     * or if the types do not match with the ones expected.
     *
     * @param Tree $tree
     * @return Closure(\stdClass $row):?AbstractGeoAnalysisView
     */
    private function viewMapper(Tree $tree): Closure
    {
        return function (stdClass $row) use ($tree): ?AbstractGeoAnalysisView {
            try {
                $geoanalysis = app($row->majgv_analysis);
                if (!($geoanalysis instanceof GeoAnalysisInterface)) {
                    return null;
                }

                $view = app()->makeWith($row->majgv_view_class, [
                    'id'                    =>  (int) $row->majgv_id,
                    'tree'                  =>  $tree,
                    'enabled'               =>  $row->majgv_status === 'enabled',
                    'description'           =>  $row->majgv_descr,
                    'geoanalysis'           =>  $geoanalysis,
                    'depth'                 =>  (int) $row->majgv_place_depth,
                    'detailed_top_places'   =>  (int) $row->majgv_top_places
                ]);

                if ($row->majgv_colors !== null && $view instanceof GeoAnalysisMap) {
                    $view->setColors($this->colorsDecoder($row->majgv_colors));
                }

                return $view instanceof AbstractGeoAnalysisView ? $view : null;
            } catch (BindingResolutionException $ex) {
                return null;
            }
        };
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

    /**
     * Create a MapColorsConfig object from a JSON column value.
     * Returns null if the JSON string is invalid, or if the colors are not valid.
     *
     * @param string $colors_config
     * @return MapColorsConfig|NULL
     */
    private function colorsDecoder(string $colors_config): ?MapColorsConfig
    {
        $colors = json_decode($colors_config, true);
        if (!is_array($colors) && count($colors) !== 4) {
            return null;
        }
        try {
            return new MapColorsConfig(
                \Spatie\Color\Factory::fromString($colors['default'] ?? ''),
                \Spatie\Color\Factory::fromString($colors['stroke'] ?? ''),
                \Spatie\Color\Factory::fromString($colors['maxvalue'] ?? ''),
                \Spatie\Color\Factory::fromString($colors['hover'] ?? '')
            );
        } catch (InvalidColorValue $ex) {
            return null;
        }
    }

    /**
     * Get a closure to filter views by enabled/disabled status
     *
     * @param bool $include_disabled
     *
     * @return Closure(AbstractGeoAnalysisView $view):bool
     */
    private function enabledFilter(bool $include_disabled): Closure
    {
        return function (AbstractGeoAnalysisView $view) use ($include_disabled): bool {
            return $include_disabled || $view->isEnabled();
        };
    }
}
