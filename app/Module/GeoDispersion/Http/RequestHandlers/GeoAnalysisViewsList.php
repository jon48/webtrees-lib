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
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Webtrees\Module\GeoDispersion\GeoDispersionModule;
use MyArtJaub\Webtrees\Module\GeoDispersion\Services\GeoAnalysisViewDataService;
use MyArtJaub\Webtrees\Module\GeoDispersion\Views\AbstractGeoAnalysisView;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Request handler for listing geographical dispersion analysis views
 *
 */
class GeoAnalysisViewsList implements RequestHandlerInterface
{
    use ViewResponseTrait;

    private ?GeoDispersionModule $module;
    private GeoAnalysisViewDataService $geoviewdata_service;

    /**
     * Constructor for GeoAnalysisViewsList Request Handler
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

        $views_list = $this->geoviewdata_service
            ->all($tree)
            ->sortBy(fn(AbstractGeoAnalysisView $view) => $view->description());

        return $this->viewResponse($this->module->name() . '::geoanalysisviews-list', [
            'module'        =>  $this->module,
            'title'         =>  I18N::translate('Geographical dispersion'),
            'tree'          =>  $tree,
            'views_list'    =>  $views_list,
            'js_script_url' =>  $this->module->assetUrl('js/geodispersion.min.js')
        ]);
    }
}
