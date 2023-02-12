<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage WelcomeBlock
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2011-2023, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\WelcomeBlock\Http\RequestHandlers;

use Fig\Http\Message\StatusCodeInterface;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Webtrees\Module\WelcomeBlock\WelcomeBlockModule;
use MyArtJaub\Webtrees\Module\WelcomeBlock\Services\MatomoStatsService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

/**
 * Request handler for retrieving Matomo statistics
 */
class MatomoStats implements RequestHandlerInterface
{
    use ViewResponseTrait;

    /**
     * @var WelcomeBlockModule|null $module
     */
    private $module;

    /**
     * @var MatomoStatsService $matomo_service
     */
    private $matomo_service;

    /**
     * Constructor for MatomoStats request handler
     * @param ModuleService $module_service
     * @param MatomoStatsService $matomo_service
     */
    public function __construct(
        ModuleService $module_service,
        MatomoStatsService $matomo_service
    ) {
        $this->module = $module_service->findByInterface(WelcomeBlockModule::class)->first();
        $this->matomo_service = $matomo_service;
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Server\RequestHandlerInterface::handle()
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->layout = 'layouts/ajax';

        if ($this->module === null) {
            return $this->viewResponse('errors/unhandled-exception', [
                'error' => 'The attached module could not be found.'
            ], StatusCodeInterface::STATUS_NOT_FOUND);
        }

        $block_id = Validator::attributes($request)->integer('block_id', -1);
        $nb_visits_year = $nb_visits_today = null;

        try {
            if ($block_id !== -1 && $this->module->isMatomoEnabled($block_id)) {
                $nb_visits_today = $this->matomo_service->visitsToday($this->module, $block_id) ?? 0;
                $nb_visits_year = ($this->matomo_service->visitsThisYear($this->module, $block_id) ?? 0)
                    + $nb_visits_today;
            }
        } catch (Throwable $ex) {
            return $this->viewResponse('errors/unhandled-exception', [
                'error' => I18N::translate('Error while retrieving Matomo statistics: ') .
                    (Auth::isAdmin() ? $ex->getMessage() : I18N::translate('Log in as admin for error details'))
            ], StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);
        }

        return $this->viewResponse($this->module->name() . '::matomo-stats', [
            'visits_year'   =>  $nb_visits_year,
            'visits_today'  =>  $nb_visits_today
        ]);
    }
}
