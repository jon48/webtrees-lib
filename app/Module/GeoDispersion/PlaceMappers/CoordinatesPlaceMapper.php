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

use Brick\Geo\BoundingBox;
use Brick\Geo\Point;
use Brick\Geo\Engine\GeometryEngineRegistry;
use Brick\Geo\Engine\PDOEngine;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Place;
use Fisharebest\Webtrees\PlaceLocation;
use Fisharebest\Webtrees\Registry;
use Illuminate\Database\Capsule\Manager as DB;
use MyArtJaub\Webtrees\Contracts\GeoDispersion\MapDefinitionInterface;
use MyArtJaub\Webtrees\Contracts\GeoDispersion\PlaceMapperInterface;
use Throwable;

/**
 * Mapper using coordinated to map a location to a GeoJson map feature.
 * This use the PlaceLocation table to determine the coordinates of the place, through the core PlaceLocation object.
 *
 * {@internal This mapper is indexing the features based on a grid to optimise the performances.
 * Using the geospatial `contains` (SQL `ST_contains`) method naively is a lot slower.}
 */
class CoordinatesPlaceMapper implements PlaceMapperInterface
{
    use PlaceMapperTrait;

    private ?string $cache_key = null;

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\GeoDispersion\PlaceMapperInterface::title()
     */
    public function title(): string
    {
        return I18N::translate('Mapping on place coordinates');
    }

    /**
     * {@inheritDoc}
     *
     * {@internal The Place is associated to a Point only.
     * PlaceLocation can calculate a BoundingBox.
     * Using a BoundingBox could make the mapping more complex and potentially arbitary.
     * Furthermore, when no coordinate is found for the place or its children, then it bubbles up to the parents.
     * This could create the unwanted side effect of a very large area to consider}
     *
     * @see \MyArtJaub\Webtrees\Contracts\GeoDispersion\PlaceMapperInterface::map()
     */
    public function map(Place $place, string $feature_property): ?string
    {
        $location = new PlaceLocation($place->gedcomName());
        $longitude = $location->longitude();
        $latitude = $location->latitude();
        if ($longitude === null || $latitude === null) {
            return null;
        }

        $features_index = $this->featuresIndex();
        if ($features_index === null) {
            return null;
        }

        $place_point = Point::xy($longitude, $latitude, $features_index['SRID']);
        $grid_box = $this->getGridCell(
            $place_point,
            $features_index['map_NE'],
            $features_index['map_SW'],
            $features_index['nb_columns']
        );
        if ($grid_box === null || !$this->setGeometryEngine()) {
            return null;
        }
        $features = $features_index['grid'][$grid_box[0]][$grid_box[1]];
        foreach ($features as $feature) {
            $geometry = $feature->getGeometry();
            if ($geometry !== null && $place_point->SRID() === $geometry->SRID() && $geometry->contains($place_point)) {
                return $feature->getProperty($feature_property);
            }
        }
        return null;
    }

    /**
     * Return the XY coordinates in a bounded grid of the cell containing a specific point.
     *
     * @param Point $point Point to find
     * @param Point $grid_NE North-East point of the bounded grid
     * @param Point $grid_SW South-West point fo the bounded grid
     * @param int $grid_columns Number of columns/rows in the grid
     * @return int[]|NULL
     */
    protected function getGridCell(Point $point, Point $grid_NE, Point $grid_SW, int $grid_columns): ?array
    {
        list($x, $y) = $point->toArray();
        list($x_max, $y_max) = $grid_NE->toArray();
        list($x_min, $y_min) = $grid_SW->toArray();

        $x_step = ($x_max - $x_min) / $grid_columns;
        $y_step = ($y_max - $y_min) / $grid_columns;

        if ($x_min <= $x && $x <= $x_max && $y_min <= $y && $y <= $y_max) {
            return [
                $x === $x_max ? $grid_columns - 1 : intval(($x - $x_min) / $x_step),
                $y === $y_max ? $grid_columns - 1 : intval(($y - $y_min) / $y_step)
            ];
        }
        return null;
    }

    /**
     * Get an indexed array of the features of the map.
     *
     * {@internal The map is divided in a grid, eacg cell containing the features which bounding box overlaps that cell.
     * The grid is computed once for each map, and cached.}
     *
     * @phpcs:ignore Generic.Files.LineLength.TooLong
     * @return array{grid: array<int, array<int, \Brick\Geo\IO\GeoJSON\Feature[]>>, nb_columns: int, map_NE: \Brick\Geo\Point, map_SW: \Brick\Geo\Point, SRID: int}|NULL
     */
    protected function featuresIndex(): ?array
    {
        $cacheKey = $this->cacheKey();
        if ($cacheKey === null) {
            return null;
        }
        return Registry::cache()->array()->remember($cacheKey, function (): ?array {
            $map_def = $this->data('map');
            if (
                !$this->setGeometryEngine()
                || $map_def === null
                || !($map_def instanceof MapDefinitionInterface)
            ) {
                return null;
            }
            $bounding_boxes = [];
            $map_bounding_box = new BoundingBox();
            $srid = 0;
            foreach ($map_def->features() as $feature) {
                $geometry = $feature->getGeometry();
                if ($geometry === null) {
                    continue;
                }
                $srid = $geometry->SRID();
                $bounding_box = $geometry->getBoundingBox();
                $bounding_boxes[] = [$feature, $bounding_box];
                $map_bounding_box = $map_bounding_box->extendedWithBoundingBox($bounding_box);
            }
            $grid_columns = count($bounding_boxes);
            $grid = array_fill(0, $grid_columns, array_fill(0, $grid_columns, []));
            $map_NE = $map_bounding_box->getNorthEast();
            $map_SW = $map_bounding_box->getSouthWest();
            foreach ($bounding_boxes as $item) {
                $grid_box_SW = $this->getGridCell($item[1]->getSouthWest(), $map_NE, $map_SW, $grid_columns) ?? [1, 1];
                $grid_box_NE = $this->getGridCell($item[1]->getNorthEast(), $map_NE, $map_SW, $grid_columns) ?? [0, 0];
                for ($i = $grid_box_SW[0]; $i <= $grid_box_NE[0]; $i++) {
                    for ($j = $grid_box_SW[1]; $j <= $grid_box_NE[1]; $j++) {
                        $grid[$i][$j][] = $item[0];
                    }
                }
            }
            return [
                'grid'          =>  $grid,
                'nb_columns'    =>  $grid_columns,
                'map_NE'        =>  $map_NE,
                'map_SW'        =>  $map_SW,
                'SRID'          =>  $srid
            ];
        });
    }

    /**
     * Set the Brick Geo Engine to use the database for geospatial computations.
     * The engine is set only if it has not been set beforehand.
     *
     * @return bool
     */
    protected function setGeometryEngine(): bool
    {
        try {
            if (!GeometryEngineRegistry::has()) {
                GeometryEngineRegistry::set(new PDOEngine(DB::connection()->getPdo()));
            }
            $point = Point::xy(1, 1);
            return $point->equals($point);
        } catch (Throwable $ex) {
        }
        return false;
    }

    /**
     * Get the key to cache the indexed grid of features.
     *
     * @return string|NULL
     */
    protected function cacheKey(): ?string
    {
        if ($this->cache_key === null) {
            $map_def = $this->data('map');
            if ($map_def === null || !($map_def instanceof MapDefinitionInterface)) {
                return null;
            }
            return spl_object_id($this) . '-map-' . $map_def->id();
        }
        return $this->cache_key;
    }
}
