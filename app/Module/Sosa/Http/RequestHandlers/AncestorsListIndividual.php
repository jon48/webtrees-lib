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

use Fig\Http\Message\StatusCodeInterface;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\DefaultUser;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\Http\Exceptions\HttpNotFoundException;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Webtrees\Module\Sosa\SosaModule;
use MyArtJaub\Webtrees\Module\Sosa\Services\SosaRecordsService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use stdClass;

/**
 * Request handler for tab listing the Sosa individuals. Provide an AJAX response.
 */
class AncestorsListIndividual implements RequestHandlerInterface
{
    use ViewResponseTrait;

    /**
     * @var SosaModule|null $module
     */
    private $module;

    /**
     * @var SosaRecordsService $sosa_record_service
     */
    private $sosa_record_service;

    /**
     * Constructor for AncestorsListIndividual Request Handler
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
        $this->layout = 'layouts/ajax';

        if ($this->module === null) {
            throw new HttpNotFoundException(I18N::translate('The attached module could not be found.'));
        }

        $tree = Validator::attributes($request)->tree();
        $user = Auth::check() ? Validator::attributes($request)->user() : new DefaultUser();
        $current_gen = Validator::attributes($request)->integer('gen', 0);

        if ($current_gen <= 0) {
            return Registry::responseFactory()->response(
                'Invalid generation',
                StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY
            );
        }

        $list_ancestors = $this->sosa_record_service->listAncestorsAtGeneration($tree, $user, $current_gen);
        $nb_ancestors_all = $list_ancestors->count();

        /** @var \Illuminate\Support\Collection<int, \Fisharebest\Webtrees\Individual> $list_ancestors */
        $list_ancestors = $list_ancestors
            ->filter(function (stdClass $value) use ($tree): bool {
                $indi = Registry::individualFactory()->make($value->majs_i_id, $tree);
                return $indi !== null && $indi->canShowName();
            })
            ->mapWithKeys(function (stdClass $value) use ($tree): array {
                $indi = Registry::individualFactory()->make($value->majs_i_id, $tree);
                return [(int) $value->majs_sosa => $indi];
            });

        $nb_ancestors_shown = $list_ancestors->count();

        return $this->viewResponse($this->module->name() . '::list-ancestors-indi-tab', [
            'module_name'       =>  $this->module->name(),
            'title'             =>  I18N::translate('Sosa Ancestors'),
            'tree'              =>  $tree,
            'list_ancestors'    =>  $list_ancestors,
            'nb_ancestors_all'  =>  $nb_ancestors_all,
            'nb_ancestors_theor' =>  pow(2, $current_gen - 1),
            'nb_ancestors_shown' =>  $nb_ancestors_shown
        ]);
    }
}
