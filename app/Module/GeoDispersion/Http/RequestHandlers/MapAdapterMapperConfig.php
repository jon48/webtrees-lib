<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage GeoDispersion
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2023, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\GeoDispersion\Http\RequestHandlers;

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\Http\Exceptions\HttpNotFoundException;
use Fisharebest\Webtrees\Services\ModuleService;
use Illuminate\Contracts\Container\BindingResolutionException;
use MyArtJaub\Webtrees\Contracts\GeoDispersion\PlaceMapperInterface;
use MyArtJaub\Webtrees\Module\GeoDispersion\GeoDispersionModule;
use MyArtJaub\Webtrees\Module\GeoDispersion\Services\MapAdapterDataService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Request handler for displaying configuration of a place mapper.
 */
class MapAdapterMapperConfig implements RequestHandlerInterface
{
    use ViewResponseTrait;

    private ?GeoDispersionModule $module;
    private MapAdapterDataService $mapadapter_data_service;

    /**
     * Constructor for MapAdapterMapperConfig Request Handler
     *
     * @param ModuleService $module_service
     * @param MapAdapterDataService $mapadapter_data_service
     */
    public function __construct(
        ModuleService $module_service,
        MapAdapterDataService $mapadapter_data_service
    ) {
        $this->module = $module_service->findByInterface(GeoDispersionModule::class)->first();
        $this->mapadapter_data_service = $mapadapter_data_service;
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Server\RequestHandlerInterface::handle()
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->layout = 'layouts/ajax';

        if ($this->module === null) {
            throw new HttpNotFoundException(I18N::translate('The attached module could not be found.'));
        }
        $tree = Validator::attributes($request)->tree();

        $adapter_id = Validator::attributes($request)->integer('adapter_id', -1);
        $map_adapter = $this->mapadapter_data_service->find($adapter_id);

        $mapper_class = Validator::queryParams($request)->string('mapper', '');
        $mapper = null;
        if ($mapper_class === '' && $map_adapter !== null) {
            $mapper = $map_adapter->placeMapper();
        } else {
            try {
                $mapper = app($mapper_class);
            } catch (BindingResolutionException $ex) {
            }

            if (
                $mapper !== null && $map_adapter !== null &&
                get_class($map_adapter->placeMapper()) === get_class($mapper)
            ) {
                $mapper = $map_adapter->placeMapper();
            }
        }

        if ($mapper === null || !($mapper instanceof PlaceMapperInterface)) {
            throw new HttpNotFoundException(
                I18N::translate('The configuration for the place mapper could not be found.')
            );
        }

        return $this->viewResponse('layouts/ajax', [
            'content' => $mapper->config()->configContent($this->module, $tree)
        ]);
    }
}
