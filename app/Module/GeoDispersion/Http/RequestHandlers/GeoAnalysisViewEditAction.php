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
use Illuminate\Contracts\Container\BindingResolutionException;
use MyArtJaub\Webtrees\Contracts\GeoDispersion\GeoAnalysisInterface;
use MyArtJaub\Webtrees\Module\GeoDispersion\GeoDispersionModule;
use MyArtJaub\Webtrees\Module\GeoDispersion\Services\GeoAnalysisViewDataService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

/**
 * Request handler for editing a geographical analysis view.
 */
class GeoAnalysisViewEditAction implements RequestHandlerInterface
{
    private ?GeoDispersionModule $module;
    private GeoAnalysisViewDataService $geoview_data_service;

    /**
     * Constructor for GeoAnalysisViewEditAction Request Handler
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


        $view_id = Validator::attributes($request)->integer('view_id', -1);
        $view = $this->geoview_data_service->find($tree, $view_id, true);

        $description    = Validator::parsedBody($request)->string('view_description', '');
        $place_depth    = Validator::parsedBody($request)->integer('view_depth', 1);
        $top_places     = Validator::parsedBody($request)->integer('view_top_places', 0);

        $analysis = null;
        try {
            $analysis = app(Validator::parsedBody($request)->string('view_analysis', ''));
        } catch (BindingResolutionException $ex) {
        }

        if (
            $view === null
            || $analysis === null || !($analysis instanceof GeoAnalysisInterface)
            || $place_depth <= 0 && $top_places < 0
        ) {
            FlashMessages::addMessage(
                I18N::translate('The parameters for view with ID “%s” are not valid.', I18N::number($view_id)),
                'danger'
            );
            return redirect($admin_config_route);
        }

        $new_view = $view
            ->with($view->isEnabled(), $description, $analysis, $place_depth, $top_places)
            ->withGlobalSettingsUpdate($request);

        try {
            $this->geoview_data_service->update($new_view);
            FlashMessages::addMessage(
                I18N::translate('The geographical dispersion analysis view has been successfully updated.'),
                'success'
            );
            //phpcs:ignore Generic.Files.LineLength.TooLong
            Log::addConfigurationLog('Module ' . $this->module->title() . ' : View “' . $view->id() . '” has been updated.');
        } catch (Throwable $ex) {
            FlashMessages::addMessage(
                I18N::translate('An error occured while updating the geographical dispersion analysis view.'),
                'danger'
            );
            //phpcs:ignore Generic.Files.LineLength.TooLong
            Log::addErrorLog('Module ' . $this->module->title() . ' : Error when updating view “' . $view->id() . '”: ' . $ex->getMessage());
        }

        return redirect($admin_config_route);
    }
}
