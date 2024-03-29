<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Sosa
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2022, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\Sosa\Http\RequestHandlers;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\DefaultUser;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\Http\Exceptions\HttpNotFoundException;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Webtrees\Module\Sosa\SosaModule;
use MyArtJaub\Webtrees\Module\Sosa\Services\SosaRecordsService;
use MyArtJaub\Webtrees\Module\Sosa\Services\SosaStatisticsService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Request handler for listing Sosa ancestors. Only handle the main page, with generation selection.
 */
class AncestorsList implements RequestHandlerInterface
{
    use ViewResponseTrait;

    private ?SosaModule $module;

    /**
     * Constructor for AncestorsList Request Handler
     *
     * @param ModuleService $module_service
     */
    public function __construct(ModuleService $module_service)
    {
        $this->module = $module_service->findByInterface(SosaModule::class)->first();
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
        $user = Auth::check() ? Validator::attributes($request)->user() : new DefaultUser();

        /** @var SosaStatisticsService $sosa_stats_service */
        $sosa_stats_service = app()->makeWith(SosaStatisticsService::class, ['tree' => $tree, 'user' => $user]);

        $current_gen = Validator::queryParams($request)->integer(
            'gen',
            Validator::attributes($request)->integer('gen', 0)
        );

        return $this->viewResponse($this->module->name() . '::list-ancestors-page', [
            'module_name'       =>  $this->module->name(),
            'title'             =>  I18N::translate('Sosa Ancestors'),
            'tree'              =>  $tree,
            'root_indi'         =>  $sosa_stats_service->rootIndividual(),
            'max_gen'           =>  $sosa_stats_service->maxGeneration(),
            'current_gen'       =>  $current_gen
        ]);
    }
}
