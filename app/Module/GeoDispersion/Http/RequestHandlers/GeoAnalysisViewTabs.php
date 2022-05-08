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

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\Http\Exceptions\HttpNotFoundException;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Webtrees\Module\GeoDispersion\GeoDispersionModule;
use MyArtJaub\Webtrees\Module\GeoDispersion\Services\GeoAnalysisViewDataService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Request handler for tabs displaying a geographical dispersion analysis view.
 *
 */
class GeoAnalysisViewTabs implements RequestHandlerInterface
{
    private ?GeoDispersionModule $module;
    private GeoAnalysisViewDataService $geoviewdata_service;

    /**
     * Constructor for GeoAnalysisMapsList Request Handler
     *
     * @param ModuleService $module_service
     */
    public function __construct(
        ModuleService $module_service,
        GeoAnalysisViewDataService $geoviewdata_service
    ) {
        $this->module = $module_service->findByInterface(GeoDispersionModule::class)->first();
        $this->geoviewdata_service = $geoviewdata_service;
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
        $view_id = Validator::attributes($request)->integer('view_id', -1);

        $view = $this->geoviewdata_service->find($tree, $view_id);

        if ($view === null) {
            throw new HttpNotFoundException(I18N::translate('The requested dispersion analysis does not exist.'));
        }

        $results = $view->analysis()->results($tree, $view->placesDepth());

        $params = [
            'module_name'   =>  $this->module->name(),
            'tree'          =>  $tree,
            'view'          =>  $view,
            'items_descr'   =>  $view->analysis()->itemsDescription()
        ];
        $response = [
            'global'    =>  view('layouts/ajax', [
                'content' =>    $view->globalTabContent($this->module, $results->global(), $params)
            ]),
            'detailed'  => view('layouts/ajax', [
                'content' =>    $view->detailedTabContent($this->module, $results->sortedDetailed(), $params)
            ])
        ];

        return response($response);
    }
}
