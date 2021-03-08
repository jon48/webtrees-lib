<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage WelcomeBlock
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2011-2020, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\WelcomeBlock\Http\RequestHandlers;

use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Webtrees\Module\WelcomeBlock\WelcomeBlockModule;
use MyArtJaub\Webtrees\Module\WelcomeBlock\Services\MatomoStatsService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

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
            ]);
        }

        $block_id = filter_var($request->getAttribute('block_id'), FILTER_VALIDATE_INT);
        $nb_visits_year = $nb_visits_today = null;

        if ($block_id !== false && $this->module->isMatomoEnabled($block_id)) {
            $nb_visits_today = (int) $this->matomo_service->visitsToday($this->module, $block_id);
            $nb_visits_year = (int) $this->matomo_service->visitsThisYear($this->module, $block_id) + $nb_visits_today;
        }

        return $this->viewResponse($this->module->name() . '::matomo-stats', [
            'visits_year'   =>  $nb_visits_year,
            'visits_today'  =>  $nb_visits_today
        ]);
    }
}
