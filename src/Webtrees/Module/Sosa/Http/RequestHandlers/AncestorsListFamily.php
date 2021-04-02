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

use Fig\Http\Message\StatusCodeInterface;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\DefaultUser;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Exceptions\HttpNotFoundException;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Webtrees\Module\Sosa\SosaModule;
use MyArtJaub\Webtrees\Module\Sosa\Services\SosaRecordsService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use stdClass;

/**
 * Request handler for tab listing the Sosa families. Provide an AJAX response.
 */
class AncestorsListFamily implements RequestHandlerInterface
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
     * Constructor for AncestorsListFamily Request Handler
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

        $tree = $request->getAttribute('tree');
        assert($tree instanceof Tree);

        $user = Auth::check() ? $request->getAttribute('user') : new DefaultUser();

        $current_gen = (int) ($request->getAttribute('gen') ?? 0);

        if ($current_gen <= 0) {
            return response('Invalid generation', StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY);
        }

        $list_families = $this->sosa_record_service->listAncestorFamiliesAtGeneration($tree, $user, $current_gen);
        $nb_families_all = $list_families->count();

        /** @var \Illuminate\Support\Collection<int, \Fisharebest\Webtrees\Family> $list_families */
        $list_families = $list_families->mapWithKeys(function (stdClass $value) use ($tree): ?array {
                $fam = Registry::familyFactory()->make($value->f_id, $tree);
                return ($fam !== null && $fam->canShow()) ? [(int) $value->majs_sosa => $fam] : null;
        })->filter();

        $nb_families_shown = $list_families->count();

        return $this->viewResponse($this->module->name() . '::list-ancestors-fam-tab', [
            'module_name'       =>  $this->module->name(),
            'title'             =>  I18N::translate('Sosa Ancestors'),
            'tree'              =>  $tree,
            'list_families'     =>  $list_families,
            'nb_families_all'   =>  $nb_families_all,
            'nb_families_shown' =>  $nb_families_shown
        ]);
    }
}
