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
use MyArtJaub\Webtrees\Contracts\GeoDispersion\PlaceMapperInterface;
use MyArtJaub\Webtrees\Module\GeoDispersion\GeoDispersionModule;
use MyArtJaub\Webtrees\Module\GeoDispersion\Services\MapAdapterDataService;
use MyArtJaub\Webtrees\Module\GeoDispersion\Services\MapDefinitionsService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

/**
 * Request handler for editing a a geographical analysis map adapter.
 */
class MapAdapterEditAction implements RequestHandlerInterface
{
    private ?GeoDispersionModule $module;
    private MapAdapterDataService $mapadapter_data_service;
    private MapDefinitionsService $map_definition_service;

    /**
     * Constructor for MapAdapterEditAction Request Handler
     *
     * @param ModuleService $module_service
     * @param MapAdapterDataService $mapadapter_data_service
     * @param MapDefinitionsService $map_definition_service
     */
    public function __construct(
        ModuleService $module_service,
        MapAdapterDataService $mapadapter_data_service,
        MapDefinitionsService $map_definition_service
    ) {
        $this->module = $module_service->findByInterface(GeoDispersionModule::class)->first();
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

        $adapter_id = Validator::attributes($request)->integer('adapter_id', -1);
        $map_adapter = $this->mapadapter_data_service->find($adapter_id);

        $map = $this->map_definition_service->find(Validator::parsedBody($request)->string('map_adapter_map', ''));
        $mapping_property   = Validator::parsedBody($request)->string('map_adapter_property_selected', '');

        $mapper = null;
        try {
            $mapper = app(Validator::parsedBody($request)->string('map_adapter_mapper', ''));
        } catch (BindingResolutionException $ex) {
        }

        if ($map_adapter === null || $map === null || $mapper === null || !($mapper instanceof PlaceMapperInterface)) {
            FlashMessages::addMessage(
                I18N::translate('The parameters for the map configuration are not valid.'),
                'danger'
            );
            return Registry::responseFactory()->redirect(AdminConfigPage::class, ['tree' => $tree->name()]);
        }

        $mapper->setConfig($mapper->config()->withConfigUpdate($request));
        $new_map_adapter = $map_adapter->with($map, $mapper, $mapping_property);
        try {
            $this->mapadapter_data_service->update($new_map_adapter);
            FlashMessages::addMessage(
                I18N::translate('The map configuration has been successfully updated.'),
                'success'
            );
            //phpcs:ignore Generic.Files.LineLength.TooLong
            Log::addConfigurationLog('Module ' . $this->module->title() . ' : Map Adapter “' . $map_adapter->id() . '” has been updated.');
        } catch (Throwable $ex) {
            FlashMessages::addMessage(
                I18N::translate('An error occured while updating the map configuration.'),
                'danger'
            );
            //phpcs:ignore Generic.Files.LineLength.TooLong
            Log::addErrorLog('Module ' . $this->module->title() . ' : Error when updating Map Adapter “' . $map_adapter->id() . '”: ' . $ex->getMessage());
        }

        return Registry::responseFactory()->redirect(MapAdapterEditPage::class, [
            'tree' => $tree->name(),
            'adapter_id' => $map_adapter->id()
        ]);
    }
}
