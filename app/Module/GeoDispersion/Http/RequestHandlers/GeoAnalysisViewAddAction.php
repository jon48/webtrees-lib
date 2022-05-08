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
use Illuminate\Contracts\Container\BindingResolutionException;
use MyArtJaub\Webtrees\Contracts\GeoDispersion\GeoAnalysisInterface;
use MyArtJaub\Webtrees\Module\GeoDispersion\GeoDispersionModule;
use MyArtJaub\Webtrees\Module\GeoDispersion\Services\GeoAnalysisViewDataService;
use MyArtJaub\Webtrees\Module\GeoDispersion\Views\GeoAnalysisMap;
use MyArtJaub\Webtrees\Module\GeoDispersion\Views\GeoAnalysisTable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Request handler for adding a geographical analysis view.
 */
class GeoAnalysisViewAddAction implements RequestHandlerInterface
{
    private ?GeoDispersionModule $module;
    private GeoAnalysisViewDataService $geoview_data_service;

    /**
     * Constructor for GeoAnalysisViewAddAction Request Handler
     *
     * @param ModuleService $module_service
     * @param GeoAnalysisViewDataService $geoview_data_service
     */
    public function __construct(ModuleService $module_service, GeoAnalysisViewDataService $geoview_data_service)
    {
        $this->module = $module_service->findByInterface(GeoDispersionModule::class)->first();
        $this->geoview_data_service = $geoview_data_service;
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

        $type           = Validator::parsedBody($request)->isInArray(['table', 'map'])->string('view_type', '');
        $description    = Validator::parsedBody($request)->string('view_description', '');
        $place_depth    = Validator::parsedBody($request)->integer('view_depth', 1);

        $analysis = null;
        try {
            $analysis = app(Validator::parsedBody($request)->string('view_analysis', ''));
        } catch (BindingResolutionException $ex) {
        }

        if ($type === '' || $place_depth <= 0 || $analysis === null || !($analysis instanceof GeoAnalysisInterface)) {
            FlashMessages::addMessage(
                I18N::translate('The parameters for the new view are not valid.'),
                'danger'
            );
            return redirect($admin_config_route);
        }

        if ($type === 'map') {
            $new_view = new GeoAnalysisMap(0, $tree, true, $description, $analysis, $place_depth);
        } else {
            $new_view = new GeoAnalysisTable(0, $tree, true, $description, $analysis, $place_depth);
        }

        $new_view_id = $this->geoview_data_service->insertGetId($new_view);
        if ($new_view_id > 0) {
            FlashMessages::addMessage(
                I18N::translate('The geographical dispersion analysis view has been successfully added.'),
                'success'
            );
            //phpcs:ignore Generic.Files.LineLength.TooLong
            Log::addConfigurationLog('Module ' . $this->module->title() . ' : View “' . $new_view_id . '” has been added.');
            return redirect(
                route(GeoAnalysisViewEditPage::class, ['tree' => $tree->name(), 'view_id' => $new_view_id ])
            );
        } else {
            FlashMessages::addMessage(
                I18N::translate('An error occured while adding the geographical dispersion analysis view.'),
                'danger'
            );
            //phpcs:ignore Generic.Files.LineLength.TooLong
            Log::addConfigurationLog('Module ' . $this->module->title() . ' : A new View could not be added. See error log.');
            return redirect($admin_config_route);
        }
    }
}
