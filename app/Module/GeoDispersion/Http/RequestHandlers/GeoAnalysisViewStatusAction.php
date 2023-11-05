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

use Fisharebest\Webtrees\FlashMessages;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Log;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\Http\RequestHandlers\HomePage;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Webtrees\Module\GeoDispersion\GeoDispersionModule;
use MyArtJaub\Webtrees\Module\GeoDispersion\Services\GeoAnalysisViewDataService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

/**
 * Request handler for updating the status of a geographical analysis view.
 */
class GeoAnalysisViewStatusAction implements RequestHandlerInterface
{
    private ?GeoDispersionModule $module;
    private GeoAnalysisViewDataService $geoview_data_service;

    /**
     * Constructor for GeoAnalysisViewStatusAction Request Handler
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

        if ($this->module === null) {
            FlashMessages::addMessage(
                I18N::translate('The attached module could not be found.'),
                'danger'
            );
            return Registry::responseFactory()->redirect(HomePage::class, ['tree' => $tree->name()]);
        }

        $view_id = Validator::attributes($request)->integer('view_id', -1);
        $view = $this->geoview_data_service->find($tree, $view_id, true);

        if ($view === null) {
            FlashMessages::addMessage(
                I18N::translate('The view with ID “%s” does not exist.', I18N::number($view_id)),
                'danger'
            );
            return Registry::responseFactory()->redirect(AdminConfigPage::class, ['tree' => $tree->name()]);
        }

        try {
            $this->geoview_data_service->updateStatus($view, Validator::attributes($request)->boolean('enable', false));
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

        return Registry::responseFactory()->redirect(AdminConfigPage::class, ['tree' => $tree->name()]);
    }
}
