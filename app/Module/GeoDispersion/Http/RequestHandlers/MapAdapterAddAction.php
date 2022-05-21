<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage GeoDispersion
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2022, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\GeoDispersion\Http\RequestHandlers;

use Fisharebest\Webtrees\FlashMessages;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Log;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\Services\ModuleService;
use Illuminate\Contracts\Container\BindingResolutionException;
use MyArtJaub\Webtrees\Common\GeoDispersion\Config\MapViewConfig;
use MyArtJaub\Webtrees\Contracts\GeoDispersion\PlaceMapperInterface;
use MyArtJaub\Webtrees\Module\GeoDispersion\GeoDispersionModule;
use MyArtJaub\Webtrees\Module\GeoDispersion\Model\GeoAnalysisMapAdapter;
use MyArtJaub\Webtrees\Module\GeoDispersion\Services\GeoAnalysisViewDataService;
use MyArtJaub\Webtrees\Module\GeoDispersion\Services\MapAdapterDataService;
use MyArtJaub\Webtrees\Module\GeoDispersion\Services\MapDefinitionsService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Request handler for adding a new geographical analysis map adapter.
 */
class MapAdapterAddAction implements RequestHandlerInterface
{
    private ?GeoDispersionModule $module;
    private GeoAnalysisViewDataService $geoview_data_service;
    private MapAdapterDataService $mapadapter_data_service;
    private MapDefinitionsService $map_definition_service;

    /**
     * Constructor for MapAdapterAddAction Request Handler
     *
     * @param ModuleService $module_service
     * @param GeoAnalysisViewDataService $geoview_data_service
     * @param MapAdapterDataService $mapadapter_data_service
     * @param MapDefinitionsService $map_definition_service
     */
    public function __construct(
        ModuleService $module_service,
        GeoAnalysisViewDataService $geoview_data_service,
        MapAdapterDataService $mapadapter_data_service,
        MapDefinitionsService $map_definition_service
    ) {
        $this->module = $module_service->findByInterface(GeoDispersionModule::class)->first();
        $this->geoview_data_service = $geoview_data_service;
        $this->mapadapter_data_service = $mapadapter_data_service;
        $this->map_definition_service = $map_definition_service;
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Server\RequestHandlerInterface::handle()
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $tree = Validator::attributes($request)->tree();

        if ($this->module === null) {
            FlashMessages::addMessage(
                I18N::translate('The attached module could not be found.'),
                'danger'
            );
            return Registry::responseFactory()->redirect(AdminConfigPage::class, ['tree' => $tree->name()]);
        }

        $view_id = Validator::attributes($request)->integer('view_id', -1);
        $view = $this->geoview_data_service->find($tree, $view_id);

        $map = $this->map_definition_service->find(Validator::parsedBody($request)->string('map_adapter_map', ''));
        $mapping_property   = Validator::parsedBody($request)->string('map_adapter_property_selected', '');

        $mapper = null;
        try {
            $mapper = app(Validator::parsedBody($request)->string('map_adapter_mapper', ''));
        } catch (BindingResolutionException $ex) {
        }

        if ($view === null || $map === null || $mapper === null || !($mapper instanceof PlaceMapperInterface)) {
            FlashMessages::addMessage(
                I18N::translate('The parameters for the map configuration are not valid.'),
                'danger'
            );
            return Registry::responseFactory()->redirect(AdminConfigPage::class, ['tree' => $tree->name()]);
        }

        $new_adapter_id = $this->mapadapter_data_service->insertGetId(
            new GeoAnalysisMapAdapter(
                0,
                $view_id,
                $map,
                $mapper,
                new MapViewConfig($mapping_property, $mapper->config()->withConfigUpdate($request))
            )
        );
        if ($new_adapter_id > 0) {
            FlashMessages::addMessage(
                I18N::translate('The map configuration has been successfully added.'),
                'success'
            );
            //phpcs:ignore Generic.Files.LineLength.TooLong
            Log::addConfigurationLog('Module ' . $this->module->title() . ' : Map Adapter “' . $new_adapter_id . '” has been added.');
        } else {
            FlashMessages::addMessage(
                I18N::translate('An error occured while adding a new map configuration.'),
                'danger'
            );
            //phpcs:ignore Generic.Files.LineLength.TooLong
            Log::addConfigurationLog('Module ' . $this->module->title() . ' : Map Adapter could not be added. See error log.');
        }

        return Registry::responseFactory()->redirect(GeoAnalysisViewEditPage::class, [
            'tree' => $tree->name(),
            'view_id' => $view_id
        ]);
    }
}
