<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage GeoDispersion
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2021-2023, Jonathan Jaubart
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
use MyArtJaub\Webtrees\Contracts\GeoDispersion\GeoAnalysisInterface;
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
    /**
     * Find a Geographical dispersion analysis view by ID
     *
     * @param Tree $tree
     * @param int $id
     * @return AbstractGeoAnalysisView|NULL
     */
    public function find(Tree $tree, int $id, bool $include_disabled = false): ?AbstractGeoAnalysisView
    {
        return $this->all($tree, $include_disabled)
            ->first(static fn(AbstractGeoAnalysisView $view): bool => $view->id() === $id);
    }

    /**
     * Get all Geographical dispersion analysis views, with or without the disabled ones.
     *
     * {@internal It would ignore any view for which the class could not be loaded by the container}
     *
     * @param Tree $tree
     * @param bool $include_disabled
     * @return Collection<int, AbstractGeoAnalysisView>
     */
    public function all(Tree $tree, bool $include_disabled = false): Collection
    {
        return Registry::cache()->array()->remember(
            'all-geodispersion-views',
            fn (): Collection =>
                DB::table('maj_geodisp_views')
                    ->select('maj_geodisp_views.*')
                    ->where('majgv_gedcom_id', '=', $tree->id())
                    ->get()
                    ->map($this->viewMapper($tree))
                    ->filter(static fn (?AbstractGeoAnalysisView $view): bool => $view !== null)
                    ->filter($this->enabledFilter($include_disabled))
        );
    }

    /**
     * Insert a geographical dispersion analysis view object in the database.
     *
     * @param AbstractGeoAnalysisView $view
     * @return int
     */
    public function insertGetId(AbstractGeoAnalysisView $view): int
    {
        return DB::table('maj_geodisp_views')
            ->insertGetId([
                'majgv_gedcom_id' => $view->tree()->id(),
                'majgv_view_class' => get_class($view),
                'majgv_status' => $view->isEnabled() ? 'enabled' : 'disabled',
                'majgv_descr' => mb_substr($view->description(), 0, 248),
                'majgv_analysis' => get_class($view->analysis()),
                'majgv_place_depth' => $view->placesDepth()
            ]);
    }

    /**
     * Update a geographical dispersion analysis view object in the database.
     *
     * @param AbstractGeoAnalysisView $view
     * @return int
     */
    public function update(AbstractGeoAnalysisView $view): int
    {
        return DB::table('maj_geodisp_views')
            ->where('majgv_id', '=', $view->id())
            ->update([
                'majgv_gedcom_id' => $view->tree()->id(),
                'majgv_view_class' => get_class($view),
                'majgv_status' => $view->isEnabled() ? 'enabled' : 'disabled',
                'majgv_descr' => mb_substr($view->description(), 0, 248),
                'majgv_analysis' => get_class($view->analysis()),
                'majgv_place_depth' => $view->placesDepth(),
                'majgv_top_places' => $view->numberTopPlaces(),
                'majgv_colors' => $view instanceof GeoAnalysisMap ? json_encode($view->colors()) : null
            ]);
    }

    /**
     * Update the status of a geographical dispersion analysis view object in the database.
     *
     * @param AbstractGeoAnalysisView $view
     * @param bool $status
     * @return int
     */
    public function updateStatus(AbstractGeoAnalysisView $view, bool $status): int
    {
        return DB::table('maj_geodisp_views')
            ->where('majgv_id', '=', $view->id())
            ->update(['majgv_status' => $status ? 'enabled' : 'disabled']);
    }

    /**
     * Delete a geographical dispersion analysis view object from the database.
     *
     * @param AbstractGeoAnalysisView $view
     * @return int
     */
    public function delete(AbstractGeoAnalysisView $view): int
    {
        return DB::table('maj_geodisp_views')
            ->where('majgv_id', '=', $view->id())
            ->delete();
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
                    $view = $view->withColors($this->colorsDecoder($row->majgv_colors));
                }

                return $view instanceof AbstractGeoAnalysisView ? $view : null;
            } catch (BindingResolutionException $ex) {
                return null;
            }
        };
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
