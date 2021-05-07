<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage GeoDispersion
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2021, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\GeoDispersion\Schema;

use Fisharebest\Webtrees\FlashMessages;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Schema\MigrationInterface;
use Fisharebest\Webtrees\Services\TreeService;
use Illuminate\Database\Capsule\Manager as DB;
use MyArtJaub\Webtrees\Common\GeoDispersion\Config\MapColorsConfig;
use MyArtJaub\Webtrees\Common\GeoDispersion\Config\MapViewConfig;
use MyArtJaub\Webtrees\Module\GeoDispersion\Model\GeoAnalysisMapAdapter;
use MyArtJaub\Webtrees\Module\GeoDispersion\PlaceMappers\SimplePlaceMapper;
use MyArtJaub\Webtrees\Module\GeoDispersion\Services\GeoAnalysisViewDataService;
use MyArtJaub\Webtrees\Module\GeoDispersion\Services\MapAdapterDataService;
use MyArtJaub\Webtrees\Module\GeoDispersion\Services\MapDefinitionsService;
use MyArtJaub\Webtrees\Module\GeoDispersion\Views\GeoAnalysisMap;
use MyArtJaub\Webtrees\Module\GeoDispersion\Views\GeoAnalysisTable;
use MyArtJaub\Webtrees\Module\Sosa\GeoAnalyses\SosaByGenerationGeoAnalysis;
use Spatie\Color\Hex;
use RuntimeException;
use stdClass;

/**
 * Upgrade the database schema from version 2 to version 3 (migrated views from webtrees 1.7).
 */
class Migration2 implements MigrationInterface
{
    /**
     * Mapping from old map definitions to new ones
     * @var array<string,mixed> MAPS_XML_MAPPING
     */
    private const MAPS_XML_MAPPING = [
        'aubracmargeridebycommunes.xml' =>  'fr-area-aubrac-lot-margeride-planeze-communes',
        'calvadosbycommunes.xml'        =>  'fr-dpt-14-communes',
        'cantalbycommunes.xml'          =>  'fr-dpt-15-communes',
        'cotesdarmorbycommunes.xml'     =>  'fr-dpt-22-communes',
        'essonnebycommunes.xml'         =>  'fr-dpt-91-communes',
        'eurebycommunes.xml'            =>  'fr-dpt-27-communes',
        'eureetloirbycommunes.xml'      =>  'fr-dpt-28-communes',
        'francebydepartements.xml'      =>  'fr-metropole-departements',
        'francebyregions1970.xml'       =>  'fr-metropole-regions-1970',
        'francebyregions2016.xml'       =>  'fr-metropole-regions-2016',
        'hauteloirebycommunes.xml'      =>  'fr-dpt-43-communes',
        'illeetvilainebycommunes.xml'   =>  'fr-dpt-35-communes',
        'loiretbycommunes.xml'          =>  'fr-dpt-45-communes',
        'lozerebycodepostaux.xml'       =>  'fr-dpt-48-codespostaux',
        'lozerebycommunes.xml'          =>  'fr-dpt-48-communes',
        'mayennebycommunes.xml'         =>  'fr-dpt-53-communes',
        'oisebycommunes.xml'            =>  'fr-dpt-60-communes',
        'ornebycommunes.xml'            =>  'fr-dpt-61-communes',
        'puydedomebycommunes.xml'       =>  'fr-dpt-63-communes',
        'sarthebycommunes.xml'          =>  'fr-dpt-72-communes',
        'seinemaritimebycommunes.xml'   =>  'fr-dpt-76-communes',
        'seinesommeoisebycommunes.xml'  =>  ['fr-dpt-60-communes', 'fr-dpt-76-communes', 'fr-dpt-80-communes'],
        'valdoisebycommunes.xml'        =>  'fr-dpt-95-communes',
        'yvelinesbycommunes.xml'        =>  'fr-dpt-78-communes'
    ];

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Schema\MigrationInterface::upgrade()
     */
    public function upgrade(): void
    {
        if (!DB::schema()->hasTable('maj_geodispersion')) {
            return;
        }

        /** @var TreeService $tree_service */
        $tree_service = app(TreeService::class);
        /** @var GeoAnalysisViewDataService $geoview_data_service */
        $geoview_data_service = app(GeoAnalysisViewDataService::class);

        $existing_views = DB::table('maj_geodispersion')
            ->select()
            ->get();

        foreach ($existing_views as $old_view) {
            try {
                $tree = $tree_service->find((int) $old_view->majgd_file);
            } catch (RuntimeException $ex) {
                continue;
            }

            if ($old_view->majgd_map === null) {
                $this->migrateGeoAnalysisTable($old_view, $tree, $geoview_data_service);
            } else {
                DB::connection()->beginTransaction();
                if ($this->migrateGeoAnalysisMap($old_view, $tree, $geoview_data_service)) {
                    DB::connection()->commit();
                } else {
                    DB::connection()->rollBack();
                }
            }
        }

        DB::schema()->drop('maj_geodispersion');

        FlashMessages::addMessage(I18N::translate(
            'The geographical dispersion analyses have been migrated for webtrees 2. Please review their settings.'
        ));
    }

    /**
     * Create a Table geographical analysis view from a migrated item.
     *
     * @param stdClass $old_view
     * @param Tree $tree
     * @param GeoAnalysisViewDataService $geoview_data_service
     * @return bool
     */
    private function migrateGeoAnalysisTable(
        stdClass $old_view,
        Tree $tree,
        GeoAnalysisViewDataService $geoview_data_service
    ): bool {
        $new_view = new GeoAnalysisTable(
            0,
            $tree,
            $old_view->majgd_status === 'enabled',
            $old_view->majgd_descr,
            app(SosaByGenerationGeoAnalysis::class),
            (int) $old_view->majgd_sublevel,
            (int) $old_view->majgd_detailsgen
        );

        return $geoview_data_service->insertGetId($new_view) > 0;
    }

    /**
     * Create a Map geographical analysis view from a migrated item.
     *
     * @param stdClass $old_view
     * @param Tree $tree
     * @param GeoAnalysisViewDataService $geoview_data_service
     * @return bool
     */
    private function migrateGeoAnalysisMap(
        stdClass $old_view,
        Tree $tree,
        GeoAnalysisViewDataService $geoview_data_service
    ): bool {
        /** @var MapDefinitionsService $map_definition_service */
        $map_definition_service = app(MapDefinitionsService::class);
        /** @var MapAdapterDataService $mapadapter_data_service */
        $mapadapter_data_service = app(MapAdapterDataService::class);

        $new_view = new GeoAnalysisMap(
            0,
            $tree,
            $old_view->majgd_status === 'enabled',
            $old_view->majgd_descr,
            app(SosaByGenerationGeoAnalysis::class),
            (int) $old_view->majgd_sublevel,
            (int) $old_view->majgd_detailsgen
        );

        $view_id = $geoview_data_service->insertGetId($new_view);
        if ($view_id === 0) {
            return false;
        }
        $new_view = $new_view->withId($view_id);

        $colors = $new_view->colors();
        foreach ($this->mapIdsFromOld($old_view->majgd_map) as $new_map_id) {
            $map = $map_definition_service->find($new_map_id);
            if ($map === null) {
                return false;
            }
            $colors = $this->colorsFromMap($new_map_id);

            /** @var SimplePlaceMapper $mapper */
            $mapper = app(SimplePlaceMapper::class);
            $mapview_config = new MapViewConfig($this->mappingPropertyForMap($new_map_id), $mapper->config());
            $map_adapter = new GeoAnalysisMapAdapter(0, $view_id, $map, $mapper, $mapview_config);

            $mapadapter_data_service->insertGetId($map_adapter);
        }

        return $geoview_data_service->update($new_view->withColors($colors)) > 0;
    }

    /**
     * Get all new map definitions IDs representing an old map definition
     *
     * @param string $map_xml
     * @return array
     */
    private function mapIdsFromOld(string $map_xml): array
    {
        $mapping = self::MAPS_XML_MAPPING[$map_xml] ?? [];
        return is_array($mapping) ? $mapping : [ $mapping ];
    }

    /**
     * Get the mapping property to be used for the migrated map adapter
     *
     * @param string $map_id
     * @return string
     */
    private function mappingPropertyForMap(string $map_id): string
    {
        switch ($map_id) {
            case 'fr-metropole-regions-1970':
            case 'fr-metropole-regions-2016':
                return 'region_insee_libelle';
            case 'fr-metropole-departements':
                return 'dpt_insee_libelle';
            case 'fr-dpt-48-codespostaux':
                return 'code_postal';
            default:
                return 'commune_insee_libelle';
        }
    }

    /**
     * Get the color configuration to be used for the migrated map view
     *
     * @param string $map_id
     * @return MapColorsConfig
     */
    private function colorsFromMap(string $map_id): MapColorsConfig
    {
        $default = Hex::fromString('#f5f5f5');
        $stroke = Hex::fromString('#d5d5d5');
        $hover = Hex::fromString('#ff6600');

        switch ($map_id) {
            case 'fr-metropole-departements':
                return new MapColorsConfig($default, $stroke, Hex::fromString('#0493ab'), $hover);
            case 'fr-dpt-48-codespostaux':
                return new MapColorsConfig($default, $stroke, Hex::fromString('#44aa00'), $hover);
            default:
                return new MapColorsConfig($default, $stroke, Hex::fromString('#e2a61d'), $hover);
        }
    }
}
