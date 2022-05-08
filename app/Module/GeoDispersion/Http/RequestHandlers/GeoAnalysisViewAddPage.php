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
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\Http\Exceptions\HttpNotFoundException;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Webtrees\Module\GeoDispersion\GeoDispersionModule;
use MyArtJaub\Webtrees\Module\GeoDispersion\Services\GeoAnalysisDataService;
use MyArtJaub\Webtrees\Module\GeoDispersion\Services\GeoAnalysisService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Request handler for display configuration for a new geographical analysis view.
 */
class GeoAnalysisViewAddPage implements RequestHandlerInterface
{
    use ViewResponseTrait;

    private ?GeoDispersionModule $module;
    private GeoAnalysisService $geoanalysis_service;
    private GeoAnalysisDataService $geoanalysis_data_service;

    /**
     * Constructor for GeoAnalysisViewAddPage Request Handler
     *
     * @param ModuleService $module_service
     * @param GeoAnalysisService $geoanalysis_service
     * @param GeoAnalysisDataService $geoanalysis_data_service
     */
    public function __construct(
        ModuleService $module_service,
        GeoAnalysisService $geoanalysis_service,
        GeoAnalysisDataService $geoanalysis_data_service
    ) {
        $this->module = $module_service->findByInterface(GeoDispersionModule::class)->first();
        $this->geoanalysis_service = $geoanalysis_service;
        $this->geoanalysis_data_service = $geoanalysis_data_service;
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
        $tree = Validator::attributes($request)->tree();

        return $this->viewResponse($this->module->name() . '::admin/view-add', [
            'module'        =>  $this->module,
            'title'         =>  I18N::translate('Add a geographical dispersion analysis view'),
            'tree'          =>  $tree,
            'geoanalysis_list'  =>  $this->geoanalysis_service->all(),
            'place_example'     =>  $this->geoanalysis_data_service->placeHierarchyExample($tree)
        ]);
    }
}
