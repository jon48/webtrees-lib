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
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\Http\Exceptions\HttpNotFoundException;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Webtrees\Module\Sosa\SosaModule;
use MyArtJaub\Webtrees\Module\Sosa\Services\SosaStatisticsService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Request handler for pedigree collapse data. Provide a JSON response.
 *
 */
class PedigreeCollapseData implements RequestHandlerInterface
{
    /**
     * @var SosaModule|null $module
     */
    private $module;

    /**
     * Constructor for PedigreeCollapseData Request Handler
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
        $pedi_collapse_data = $sosa_stats_service->pedigreeCollapseByGenerationData();

        $response = [ 'cells' => [] ];
        $last_pedi_collapse = 0;
        foreach ($pedi_collapse_data as $gen => $rec) {
            $response['cells'][$gen] = view($this->module->name() . '::components/pedigree-collapse-cell', [
                'pedi_collapse_roots'   =>  $rec['pedi_collapse_roots'],
                'pedi_collapse_xgen'    =>  $rec['pedi_collapse_xgen']
            ]);
            $last_pedi_collapse = $rec['pedi_collapse_roots'];
        }
        $response['pedi_collapse'] = I18N::percentage($last_pedi_collapse, 2);

        return Registry::responseFactory()->response($response);
    }
}
