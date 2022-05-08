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
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Webtrees\Module\GeoDispersion\GeoDispersionModule;
use MyArtJaub\Webtrees\Module\GeoDispersion\Model\GeoAnalysisMapAdapter;
use MyArtJaub\Webtrees\Module\GeoDispersion\Services\GeoAnalysisViewDataService;
use MyArtJaub\Webtrees\Module\GeoDispersion\Services\MapAdapterDataService;
use MyArtJaub\Webtrees\Module\GeoDispersion\Views\GeoAnalysisMap;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

/**
 * Request handler for deleting invalid geographical analysis map adapters for a view.
 */
class MapAdapterDeleteInvalidAction implements RequestHandlerInterface
{
    private ?GeoDispersionModule $module;
    private GeoAnalysisViewDataService $geoview_data_service;
    private MapAdapterDataService $mapadapter_data_service;

    /**
     * Constructor for MapAdapterDeleteInvalidAction Request Handler
     *
     * @param ModuleService $module_service
     * @param GeoAnalysisViewDataService $geoview_data_service
     * @param MapAdapterDataService $mapadapter_data_service
     */
    public function __construct(
        ModuleService $module_service,
        GeoAnalysisViewDataService $geoview_data_service,
        MapAdapterDataService $mapadapter_data_service
    ) {
        $this->module = $module_service->findByInterface(GeoDispersionModule::class)->first();
        $this->geoview_data_service = $geoview_data_service;
        $this->mapadapter_data_service = $mapadapter_data_service;
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
            return redirect(route(AdminConfigPage::class, ['tree' => $tree->name()]));
        }

        $view_id = Validator::attributes($request)->integer('view_id', -1);
        $view = $this->geoview_data_service->find($tree, $view_id);

        if ($view === null || !($view instanceof GeoAnalysisMap)) {
            FlashMessages::addMessage(
                I18N::translate('The view with ID “%s” does not exist.', I18N::number($view_id)),
                'danger'
            );
            return redirect(route(AdminConfigPage::class, ['tree' => $tree->name()]));
        }

        /** @var \Illuminate\Support\Collection<int> $valid_map_adapters */
        $valid_map_adapters = $this->mapadapter_data_service
            ->allForView($view)
            ->map(fn(GeoAnalysisMapAdapter $map_adapter): int => $map_adapter->id());

        try {
            $this->mapadapter_data_service->deleteInvalid($view, $valid_map_adapters);
            FlashMessages::addMessage(
                I18N::translate('The invalid map configurations have been successfully deleted.'),
                'success'
            );
        } catch (Throwable $ex) {
            FlashMessages::addMessage(
                I18N::translate('An error occured while deleting the invalid map configurations.'),
                'danger'
            );
            //phpcs:ignore Generic.Files.LineLength.TooLong
            Log::addConfigurationLog('Module ' . $this->module->title() . ' : Error when deleting invalid map configurations: ' . $ex->getMessage());
        }

        return redirect(route(GeoAnalysisViewEditPage::class, [
            'tree'      => $tree->name(),
            'view_id'   => $view_id
        ]));
    }
}
