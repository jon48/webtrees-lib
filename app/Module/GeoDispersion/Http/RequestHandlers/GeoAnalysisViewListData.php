<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage AdminTasks
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2012-2022, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\GeoDispersion\Http\RequestHandlers;

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\Http\Exceptions\HttpNotFoundException;
use Fisharebest\Webtrees\Services\DatatablesService;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Webtrees\Module\GeoDispersion\GeoDispersionModule;
use MyArtJaub\Webtrees\Module\GeoDispersion\Services\GeoAnalysisViewDataService;
use MyArtJaub\Webtrees\Module\GeoDispersion\Views\AbstractGeoAnalysisView;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Request handler for listing geographical dispersion analysis views in JSON format.
 *
 */
class GeoAnalysisViewListData implements RequestHandlerInterface
{
    private ?GeoDispersionModule $module;
    private GeoAnalysisViewDataService $geoview_data_service;

    /**
     * Constructor for GeoAnalysisViewListData Request Handler
     *
     * @param ModuleService $module_service
     * @param GeoAnalysisViewDataService $geoview_data_service
     */
    public function __construct(
        ModuleService $module_service,
        GeoAnalysisViewDataService $geoview_data_service
    ) {
        $this->module = $module_service->findByInterface(GeoDispersionModule::class)->first();
        $this->geoview_data_service = $geoview_data_service;
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Server\RequestHandlerInterface::handle()
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->module === null) {
            throw new HttpNotFoundException(I18N::translate('The attached module could not be found.'));
        }

        $tree = Validator::attributes($request)->tree();

        $module = $this->module;
        $module_name = $this->module->name();
        return response(['data' => $this->geoview_data_service->all($tree, true)
            ->map(fn(AbstractGeoAnalysisView $view) => [
                'edit' => view($module_name . '::admin/view-table-options', [
                    'view_id' => $view->id(),
                    'view_enabled' => $view->isEnabled(),
                    'view_edit_route' => route(GeoAnalysisViewEditPage::class, [
                        'tree' => $tree->name(),
                        'view_id' => $view->id()
                    ]),
                    'view_delete_route' => route(GeoAnalysisViewDeleteAction::class, [
                        'tree' => $tree->name(),
                        'view_id' => $view->id()
                    ]),
                    'view_status_route' => route(GeoAnalysisViewStatusAction::class, [
                        'tree' => $tree->name(),
                        'view_id' => $view->id(),
                        'enable' => $view->isEnabled() ? 0 : 1
                    ]),
                ]),
                'enabled' =>  [
                    'display' => view($module_name . '::components/yes-no-icons', ['yes' => $view->isEnabled()]),
                    'raw' => $view->isEnabled() ? 0 : 1
                ],
                'type' =>  $view->icon($module),
                'description' => [
                    'display' => '<bdi>' . e($view->description()) . '</bdi>',
                    'raw' => e($view->description())
                ],
                'analysis' => [
                    'display' => '<bdi>' . e($view->analysis()->title()) . '</bdi>',
                    'raw' => e($view->analysis()->title())
                ],
                'place_depth' => [
                    'display' => I18N::number($view->placesDepth()),
                    'raw' => $view->placesDepth()
                ]
            ])
        ]);
    }
}
