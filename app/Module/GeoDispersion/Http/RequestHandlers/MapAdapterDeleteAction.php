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

use Fisharebest\Webtrees\FlashMessages;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Log;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Webtrees\Module\GeoDispersion\GeoDispersionModule;
use MyArtJaub\Webtrees\Module\GeoDispersion\Services\MapAdapterDataService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Request handler for deleting a geographical analysis map adapter.
 */
class MapAdapterDeleteAction implements RequestHandlerInterface
{
    private ?GeoDispersionModule $module;
    private MapAdapterDataService $mapadapter_data_service;

    /**
     * Constructor for MapAdapterDeleteAction Request Handler
     *
     * @param ModuleService $module_service
     * @param MapAdapterDataService $mapadapter_data_service
     */
    public function __construct(ModuleService $module_service, MapAdapterDataService $mapadapter_data_service)
    {
        $this->module = $module_service->findByInterface(GeoDispersionModule::class)->first();
        $this->mapadapter_data_service = $mapadapter_data_service;
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Server\RequestHandlerInterface::handle()
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $tree = Validator::attributes($request)->tree();

        $admin_config_route = route(AdminConfigPage::class, ['tree' => $tree->name()]);

        if ($this->module === null) {
            FlashMessages::addMessage(
                I18N::translate('The attached module could not be found.'),
                'danger'
            );
            return redirect($admin_config_route);
        }

        $adapter_id = Validator::attributes($request)->integer('adapter_id', -1);
        $map_adapter = $this->mapadapter_data_service->find($adapter_id);

        if ($map_adapter === null) {
            FlashMessages::addMessage(
                I18N::translate('The map configuration with ID “%d” does not exist.', I18N::number($adapter_id)),
                'danger'
            );
            return redirect($admin_config_route);
        }

        if ($this->mapadapter_data_service->delete($map_adapter) > 0) {
            FlashMessages::addMessage(
                I18N::translate('The map configuration has been successfully deleted.'),
                'success'
            );
            //phpcs:ignore Generic.Files.LineLength.TooLong
            Log::addConfigurationLog('Module ' . $this->module->title() . ' : Map Adapter “' . $map_adapter->id() . '” has been deleted.');
        } else {
            FlashMessages::addMessage(
                I18N::translate('An error occured while deleting the map configuration.'),
                'danger'
            );
            //phpcs:ignore Generic.Files.LineLength.TooLong
            Log::addConfigurationLog('Module ' . $this->module->title() . ' : Map Adapter “' . $map_adapter->id() . '” could not be deleted. See error log.');
        }

        return redirect(route(GeoAnalysisViewEditPage::class, [
            'tree'      => $tree->name(),
            'view_id'   => $map_adapter->geoAnalysisViewId()
        ]));
    }
}
