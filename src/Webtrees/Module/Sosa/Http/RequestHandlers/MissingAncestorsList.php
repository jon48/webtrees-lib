<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Sosa
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2020, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\Sosa\Http\RequestHandlers;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\DefaultUser;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Exceptions\HttpNotFoundException;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Webtrees\Module\Sosa\SosaModule;
use MyArtJaub\Webtrees\Module\Sosa\Data\MissingAncestor;
use MyArtJaub\Webtrees\Module\Sosa\Services\SosaRecordsService;
use MyArtJaub\Webtrees\Module\Sosa\Services\SosaStatisticsService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use stdClass;

/**
 * Request handler for listing missing Sosa ancestors
 */
class MissingAncestorsList implements RequestHandlerInterface
{
    use ViewResponseTrait;

    /**
     * @var SosaModule $module
     */
    private $module;

    /**
     * @var SosaRecordsService $sosa_record_service
     */
    private $sosa_record_service;

    /**
     * Constructor for MissingAncestorsList Request Handler
     *
     * @param ModuleService $module_service
     * @param SosaRecordsService $sosa_record_service
     */
    public function __construct(
        ModuleService $module_service,
        SosaRecordsService $sosa_record_service
    ) {
        $this->module = $module_service->findByInterface(SosaModule::class)->first();
        $this->sosa_record_service = $sosa_record_service;
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

        $tree = $request->getAttribute('tree');
        assert($tree instanceof Tree);

        $user = Auth::check() ? $request->getAttribute('user') : new DefaultUser();

        /** @var SosaStatisticsService $sosa_stats_service */
        $sosa_stats_service = app()->makeWith(SosaStatisticsService::class, ['tree' => $tree, 'user' => $user]);

        $current_gen = (int) ($request->getAttribute('gen') ?? $request->getQueryParams()['gen'] ?? 0);

        $list_missing = $this->sosa_record_service->listMissingAncestorsAtGeneration($tree, $user, $current_gen);
        $nb_missing_diff = $list_missing->sum(function (stdClass $value): int {
            return ($value->majs_fat_id === null ? 1 : 0) + ($value->majs_mot_id === null ? 1 : 0);
        });

        $list_missing = $list_missing->map(function (stdClass $value) use ($tree): ?MissingAncestor {
            $indi = Registry::individualFactory()->make($value->majs_i_id, $tree);
            if ($indi !== null && $indi->canShowName()) {
                return new MissingAncestor(
                    $indi,
                    (int) $value->majs_sosa,
                    $value->majs_fat_id === null,
                    $value->majs_mot_id === null
                );
            }
            return null;
        })->filter();

        $nb_missing_shown = $list_missing->sum(function (MissingAncestor $value): int {
            return ($value->isFatherMissing() ? 1 : 0) + ($value->isMotherMissing() ? 1 : 0);
        });

        return $this->viewResponse($this->module->name() . '::list-missing-page', [
            'module_name'       =>  $this->module->name(),
            'title'             =>  I18N::translate('Missing Ancestors'),
            'tree'              =>  $tree,
            'root_indi'         =>  $sosa_stats_service->rootIndividual(),
            'max_gen'           =>  $sosa_stats_service->maxGeneration(),
            'current_gen'       =>  $current_gen,
            'list_missing'      =>  $list_missing,
            'nb_missing_diff'   =>  $nb_missing_diff,
            'nb_missing_shown'  =>  $nb_missing_shown,
            'gen_completeness'  =>
                $sosa_stats_service->totalAncestorsAtGeneration($current_gen) / pow(2, $current_gen - 1),
            'gen_potential'     =>
                $sosa_stats_service->totalAncestorsAtGeneration($current_gen - 1) / pow(2, $current_gen - 2)
        ]);
    }
}
