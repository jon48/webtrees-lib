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

namespace MyArtJaub\Webtrees\Module\GeoDispersion\Http\RequestHandlers;

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Exceptions\HttpNotFoundException;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Webtrees\Module\GeoDispersion\GeoDispersionModule;
use MyArtJaub\Webtrees\Module\GeoDispersion\Services\MapAdapterDataService;
use MyArtJaub\Webtrees\Module\GeoDispersion\Services\MapDefinitionsService;
use MyArtJaub\Webtrees\Module\GeoDispersion\Services\PlaceMapperService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Request handler for displaying configuration of a geographical analysis map adapter.
 */
class MapAdapterEditPage implements RequestHandlerInterface
{
    use ViewResponseTrait;

    private ?GeoDispersionModule $module;
    private MapAdapterDataService $mapadapter_data_service;
    private MapDefinitionsService $map_definition_service;
    private PlaceMapperService $place_mapper_service;

    /**
     * Constructor for MapAdapterEditPage Request Handler
     *
     * @param ModuleService $module_service
     * @param MapAdapterDataService $mapadapter_data_service
     * @param MapDefinitionsService $map_definition_service
     * @param PlaceMapperService $place_mapper_service
     */
    public function __construct(
        ModuleService $module_service,
        MapAdapterDataService $mapadapter_data_service,
        MapDefinitionsService $map_definition_service,
        PlaceMapperService $place_mapper_service
    ) {
        $this->module = $module_service->findByInterface(GeoDispersionModule::class)->first();
        $this->mapadapter_data_service = $mapadapter_data_service;
        $this->map_definition_service = $map_definition_service;
        $this->place_mapper_service = $place_mapper_service;
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Server\RequestHandlerInterface::handle()
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->layout = 'layouts/administration';

        if ($this->module === null) {
            throw new HttpNotFoundException(I18N::translate('The attached module could not be found.'));
        }
        $tree = $request->getAttribute('tree');
        assert($tree instanceof Tree);

        $adapter_id = (int) $request->getAttribute('adapter_id');
        $map_adapter = $this->mapadapter_data_service->find($adapter_id);

        if ($map_adapter === null) {
            throw new HttpNotFoundException(
                I18N::translate('The map configuration could not be found.')
            );
        }

        return $this->viewResponse($this->module->name() . '::admin/map-adapter-edit', [
            'module'            =>  $this->module,
            'title'             =>  I18N::translate('Edit the map configuration'),
            'tree'              =>  $tree,
            'view_id'           =>  $map_adapter->geoAnalysisViewId(),
            'map_adapter'       =>  $map_adapter,
            'maps_list'         =>  $this->map_definition_service->all(),
            'mappers_list'      =>  $this->place_mapper_service->all(),
            'route_edit'        =>  route(MapAdapterEditAction::class, [
                                        'tree' => $tree->name(),
                                        'adapter_id' => $map_adapter->id()
                                    ])
        ]);
    }
}
