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
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleChartInterface;
use Fisharebest\Webtrees\Module\ModuleChartTrait;
use Fisharebest\Webtrees\Module\ModuleGlobalInterface;
use Fisharebest\Webtrees\Module\ModuleGlobalTrait;
use Fisharebest\Webtrees\Services\MigrationService;
use MyArtJaub\Webtrees\Contracts\GeoDispersion\ModuleGeoAnalysisProviderInterface;
use MyArtJaub\Webtrees\Contracts\GeoDispersion\ModulePlaceMapperProviderInterface;
use MyArtJaub\Webtrees\Module\ModuleMyArtJaubInterface;
use MyArtJaub\Webtrees\Module\ModuleMyArtJaubTrait;
use MyArtJaub\Webtrees\Module\GeoDispersion\GeoAnalyses\AllEventsByCenturyGeoAnalysis;
use MyArtJaub\Webtrees\Module\GeoDispersion\GeoAnalyses\AllEventsByTypeGeoAnalysis;
use MyArtJaub\Webtrees\Module\GeoDispersion\Http\RequestHandlers\GeoAnalysisViewPage;
use MyArtJaub\Webtrees\Module\GeoDispersion\Http\RequestHandlers\GeoAnalysisViewTabs;
use MyArtJaub\Webtrees\Module\GeoDispersion\Http\RequestHandlers\GeoAnalysisViewsList;
use MyArtJaub\Webtrees\Module\GeoDispersion\PlaceMappers\CoordinatesPlaceMapper;
use MyArtJaub\Webtrees\Module\GeoDispersion\PlaceMappers\ReferenceTablePlaceMapper;
use MyArtJaub\Webtrees\Module\GeoDispersion\PlaceMappers\SimplePlaceMapper;
use MyArtJaub\Webtrees\Module\GeoDispersion\PlaceMappers\SimpleTopFilteredPlaceMapper;

/**
 * Geographical Dispersion Module.
 */
class GeoDispersionModule extends AbstractModule implements
    ModuleMyArtJaubInterface,
    ModuleChartInterface,
    ModuleGlobalInterface,
    ModuleGeoAnalysisProviderInterface,
    ModulePlaceMapperProviderInterface
{
    use ModuleMyArtJaubTrait {
        boot as traitBoot;
    }
    use ModuleChartTrait;
    use ModuleGlobalTrait;

    // How to update the database schema for this module
    private const SCHEMA_TARGET_VERSION   = 2;
    private const SCHEMA_SETTING_NAME     = 'MAJ_GEODISP_SCHEMA_VERSION';
    private const SCHEMA_MIGRATION_PREFIX = __NAMESPACE__ . '\Schema';

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::title()
     */
    public function title(): string
    {
        return /* I18N: Name of the “GeoDispersion” module */ I18N::translate('Geographical Dispersion');
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::description()
     */
    public function description(): string
    {
        //phpcs:ignore Generic.Files.LineLength.TooLong
        return /* I18N: Description of the “GeoDispersion” module */ I18N::translate('Perform and display geographical dispersion analysis');
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

                $router->get(GeoAnalysisViewsList::class, '/list/{tree}', GeoAnalysisViewsList::class);

                $router->attach('', '/analysisview/{tree}/{view_id}', static function (Map $router): void {
                    $router->tokens(['view_id' => '\d+']);
                    $router->get(GeoAnalysisViewPage::class, '', GeoAnalysisViewPage::class);
                    $router->get(GeoAnalysisViewTabs::class, '/tabs', GeoAnalysisViewTabs::class);
                });
            });
        });
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
