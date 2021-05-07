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

namespace MyArtJaub\Webtrees\Module\GeoDispersion;

use Aura\Router\Map;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Http\Middleware\AuthManager;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleChartInterface;
use Fisharebest\Webtrees\Module\ModuleChartTrait;
use Fisharebest\Webtrees\Module\ModuleConfigInterface;
use Fisharebest\Webtrees\Module\ModuleConfigTrait;
use Fisharebest\Webtrees\Module\ModuleGlobalInterface;
use Fisharebest\Webtrees\Module\ModuleGlobalTrait;
use Fisharebest\Webtrees\Services\MigrationService;
use MyArtJaub\Webtrees\Contracts\GeoDispersion\ModuleGeoAnalysisProviderInterface;
use MyArtJaub\Webtrees\Contracts\GeoDispersion\ModulePlaceMapperProviderInterface;
use MyArtJaub\Webtrees\Module\ModuleMyArtJaubInterface;
use MyArtJaub\Webtrees\Module\ModuleMyArtJaubTrait;
use MyArtJaub\Webtrees\Module\GeoDispersion\GeoAnalyses\AllEventsByCenturyGeoAnalysis;
use MyArtJaub\Webtrees\Module\GeoDispersion\GeoAnalyses\AllEventsByTypeGeoAnalysis;
use MyArtJaub\Webtrees\Module\GeoDispersion\Http\RequestHandlers\AdminConfigPage;
use MyArtJaub\Webtrees\Module\GeoDispersion\Http\RequestHandlers\GeoAnalysisViewAddAction;
use MyArtJaub\Webtrees\Module\GeoDispersion\Http\RequestHandlers\GeoAnalysisViewAddPage;
use MyArtJaub\Webtrees\Module\GeoDispersion\Http\RequestHandlers\GeoAnalysisViewDeleteAction;
use MyArtJaub\Webtrees\Module\GeoDispersion\Http\RequestHandlers\GeoAnalysisViewEditAction;
use MyArtJaub\Webtrees\Module\GeoDispersion\Http\RequestHandlers\GeoAnalysisViewEditPage;
use MyArtJaub\Webtrees\Module\GeoDispersion\Http\RequestHandlers\GeoAnalysisViewListData;
use MyArtJaub\Webtrees\Module\GeoDispersion\Http\RequestHandlers\GeoAnalysisViewPage;
use MyArtJaub\Webtrees\Module\GeoDispersion\Http\RequestHandlers\GeoAnalysisViewStatusAction;
use MyArtJaub\Webtrees\Module\GeoDispersion\Http\RequestHandlers\GeoAnalysisViewTabs;
use MyArtJaub\Webtrees\Module\GeoDispersion\Http\RequestHandlers\GeoAnalysisViewsList;
use MyArtJaub\Webtrees\Module\GeoDispersion\Http\RequestHandlers\MapAdapterAddAction;
use MyArtJaub\Webtrees\Module\GeoDispersion\Http\RequestHandlers\MapAdapterAddPage;
use MyArtJaub\Webtrees\Module\GeoDispersion\Http\RequestHandlers\MapAdapterDeleteAction;
use MyArtJaub\Webtrees\Module\GeoDispersion\Http\RequestHandlers\MapAdapterDeleteInvalidAction;
use MyArtJaub\Webtrees\Module\GeoDispersion\Http\RequestHandlers\MapAdapterEditAction;
use MyArtJaub\Webtrees\Module\GeoDispersion\Http\RequestHandlers\MapAdapterEditPage;
use MyArtJaub\Webtrees\Module\GeoDispersion\Http\RequestHandlers\MapAdapterMapperConfig;
use MyArtJaub\Webtrees\Module\GeoDispersion\Http\RequestHandlers\MapFeaturePropertyData;
use MyArtJaub\Webtrees\Module\GeoDispersion\PlaceMappers\CoordinatesPlaceMapper;
use MyArtJaub\Webtrees\Module\GeoDispersion\PlaceMappers\SimplePlaceMapper;
use MyArtJaub\Webtrees\Module\GeoDispersion\PlaceMappers\SimpleTopFilteredPlaceMapper;

/**
 * Geographical Dispersion Module.
 */
class GeoDispersionModule extends AbstractModule implements
    ModuleMyArtJaubInterface,
    ModuleChartInterface,
    ModuleConfigInterface,
    ModuleGlobalInterface,
    ModuleGeoAnalysisProviderInterface,
    ModulePlaceMapperProviderInterface
{
    use ModuleMyArtJaubTrait {
        boot as traitBoot;
    }
    use ModuleChartTrait;
    use ModuleConfigTrait;
    use ModuleGlobalTrait;

    // How to update the database schema for this module
    private const SCHEMA_TARGET_VERSION   = 3;
    private const SCHEMA_SETTING_NAME     = 'MAJ_GEODISP_SCHEMA_VERSION';
    private const SCHEMA_MIGRATION_PREFIX = __NAMESPACE__ . '\Schema';

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::title()
     */
    public function title(): string
    {
        return /* I18N: Name of the “GeoDispersion” module */ I18N::translate('Geographical dispersion');
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::description()
     */
    public function description(): string
    {
        //phpcs:ignore Generic.Files.LineLength.TooLong
        return /* I18N: Description of the “GeoDispersion” module */ I18N::translate('Perform and display geographical dispersion analyses.');
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::boot()
     */
    public function boot(): void
    {
        $this->traitBoot();
        app(MigrationService::class)->updateSchema(
            self::SCHEMA_MIGRATION_PREFIX,
            self::SCHEMA_SETTING_NAME,
            self::SCHEMA_TARGET_VERSION
        );
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleCustomInterface::customModuleVersion()
     */
    public function customModuleVersion(): string
    {
        return '2.1.0-v.1';
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Module\ModuleMyArtJaubInterface::loadRoutes()
     */
    public function loadRoutes(Map $router): void
    {
        $router->attach('', '', static function (Map $router): void {

            $router->attach('', '/module-maj/geodispersion', static function (Map $router): void {
                $router->attach('', '/admin', static function (Map $router): void {
                    $router->get(AdminConfigPage::class, '/config{/tree}', AdminConfigPage::class);

                    $router->attach('', '/analysis-views/{tree}', static function (Map $router): void {
                        $router->tokens(['view_id' => '\d+', 'enable' => '[01]']);
                        $router->extras([
                            'middleware' => [
                                AuthManager::class,
                            ],
                        ]);
                        $router->get(GeoAnalysisViewListData::class, '', GeoAnalysisViewListData::class);

                        $router->get(GeoAnalysisViewAddPage::class, '/add', GeoAnalysisViewAddPage::class);
                        $router->post(GeoAnalysisViewAddAction::class, '/add', GeoAnalysisViewAddAction::class);
                        $router->get(GeoAnalysisViewEditPage::class, '/{view_id}', GeoAnalysisViewEditPage::class);
                        $router->post(GeoAnalysisViewEditAction::class, '/{view_id}', GeoAnalysisViewEditAction::class);
                        //phpcs:disable Generic.Files.LineLength.TooLong
                        $router->get(GeoAnalysisViewStatusAction::class, '/{view_id}/status/{enable}', GeoAnalysisViewStatusAction::class);
                        $router->get(GeoAnalysisViewDeleteAction::class, '/{view_id}/delete', GeoAnalysisViewDeleteAction::class);
                        //phpcs:enable
                    });

                    $router->attach('', '/map-adapters/{tree}', static function (Map $router): void {
                        $router->tokens(['adapter_id' => '\d+', 'view_id' => '\d+']);
                        $router->extras([
                            'middleware' => [
                                AuthManager::class,
                            ],
                        ]);

                        $router->get(MapAdapterAddPage::class, '/add/{view_id}', MapAdapterAddPage::class);
                        $router->post(MapAdapterAddAction::class, '/add/{view_id}', MapAdapterAddAction::class);
                        $router->get(MapAdapterEditPage::class, '/{adapter_id}', MapAdapterEditPage::class);
                        $router->post(MapAdapterEditAction::class, '/{adapter_id}', MapAdapterEditAction::class);
                        //phpcs:disable Generic.Files.LineLength.TooLong
                        $router->get(MapAdapterDeleteAction::class, '/{adapter_id}/delete', MapAdapterDeleteAction::class);
                        $router->get(MapAdapterDeleteInvalidAction::class, '/delete-invalid/{view_id}', MapAdapterDeleteInvalidAction::class);
                        $router->get(MapAdapterMapperConfig::class, '/mapper/config{/adapter_id}', MapAdapterMapperConfig::class);
                        //phpcs:enable
                    });

                    //phpcs:ignore Generic.Files.LineLength.TooLong
                    $router->get(MapFeaturePropertyData::class, '/map/feature-properties{/map_id}', MapFeaturePropertyData::class);
                });

                $router->get(GeoAnalysisViewsList::class, '/list/{tree}', GeoAnalysisViewsList::class);

                $router->attach('', '/analysisview/{tree}/{view_id}', static function (Map $router): void {
                    $router->tokens(['view_id' => '\d+']);
                    $router->get(GeoAnalysisViewPage::class, '', GeoAnalysisViewPage::class);
                    $router->get(GeoAnalysisViewTabs::class, '/tabs', GeoAnalysisViewTabs::class);
                });
            });
        });
    }

    public function getConfigLink(): string
    {
        return route(AdminConfigPage::class);
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleChartInterface::chartUrl()
     */
    public function chartUrl(Individual $individual, array $parameters = []): string
    {
        return route(GeoAnalysisViewsList::class, ['tree' => $individual->tree()->name()] + $parameters);
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleChartInterface::chartMenuClass()
     */
    public function chartMenuClass(): string
    {
        return 'menu-maj-geodispersion';
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleGlobalInterface::headContent()
     */
    public function headContent(): string
    {
        return '<link rel="stylesheet" href="' . e($this->moduleCssUrl()) . '">';
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\GeoDispersion\ModulePlaceMapperProviderInterface::listPlaceMappers()
     */
    public function listPlaceMappers(): array
    {
        return [
            CoordinatesPlaceMapper::class,
            SimplePlaceMapper::class,
            SimpleTopFilteredPlaceMapper::class
        ];
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\GeoDispersion\ModuleGeoAnalysisProviderInterface::listGeoAnalyses()
     */
    public function listGeoAnalyses(): array
    {
        return [
            AllEventsByCenturyGeoAnalysis::class,
            AllEventsByTypeGeoAnalysis::class
        ];
    }
}
