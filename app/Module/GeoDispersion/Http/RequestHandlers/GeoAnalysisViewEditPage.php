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
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Http\Exceptions\HttpNotFoundException;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Webtrees\Module\Certificates\CertificatesModule;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Fisharebest\Webtrees\Services\TreeService;
use MyArtJaub\Webtrees\Module\GeoDispersion\GeoDispersionModule;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Contracts\UserInterface;
use Fisharebest\Webtrees\Exceptions\HttpAccessDeniedException;
use MyArtJaub\Webtrees\Module\GeoDispersion\Services\GeoAnalysisViewDataService;
use MyArtJaub\Webtrees\Module\GeoDispersion\Services\GeoAnalysisDataService;
use MyArtJaub\Webtrees\Module\GeoDispersion\Services\GeoAnalysisService;

/**
 * Request handler for displaying configuration of a geographical analysis view.
 */
class GeoAnalysisViewEditPage implements RequestHandlerInterface
{
    use ViewResponseTrait;

    private ?GeoDispersionModule $module;
    private GeoAnalysisViewDataService $geoview_data_service;
    private GeoAnalysisService $geoanalysis_service;
    private GeoAnalysisDataService $geoanalysis_data_service;

    /**
     * Constructor for GeoAnalysisViewEditPage Request Handler
     *
     * @param ModuleService $module_service
     * @param GeoAnalysisViewDataService $geoview_data_service
     * @param GeoAnalysisService $geoanalysis_service
     * @param GeoAnalysisDataService $geoanalysis_data_service
     */
    public function __construct(
        ModuleService $module_service,
        GeoAnalysisViewDataService $geoview_data_service,
        GeoAnalysisService $geoanalysis_service,
        GeoAnalysisDataService $geoanalysis_data_service
    ) {
        $this->module = $module_service->findByInterface(GeoDispersionModule::class)->first();
        $this->geoview_data_service = $geoview_data_service;
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
        $tree = $request->getAttribute('tree');
        assert($tree instanceof Tree);

        $view_id = (int) $request->getAttribute('view_id');
        $view = $this->geoview_data_service->find($tree, $view_id, true);

        if ($view === null) {
            throw new HttpNotFoundException(
                I18N::translate('The geographical dispersion analysis view could not be found.')
            );
        }

        return $this->viewResponse($this->module->name() . '::admin/view-edit', [
            'module'        =>  $this->module,
            'title'         =>  I18N::translate('Edit the geographical dispersion analysis view - %s', $view->type()),
            'tree'          =>  $tree,
            'view'          =>  $view,
            'geoanalysis_list'  =>  $this->geoanalysis_service->all(),
            'place_example'     =>  $this->geoanalysis_data_service->placeHierarchyExample($tree),
            'global_settings'   =>  $view->globalSettingsContent($this->module)
        ]);
    }
}
