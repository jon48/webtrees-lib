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

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Webtrees\Module\Sosa\SosaModule;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Request handler for displaying the modal window for computing Sosa ancestors
 */
class SosaComputeModal implements RequestHandlerInterface
{
    /**
     * @var SosaModule|null $module
     */
    private $module;

    /**
     * Constructor for SosaComputeModal Request Handler
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
            return Registry::responseFactory()->response(view('modals/error', [
                'title' => I18N::translate('Error'),
                'error' => I18N::translate('The attached module could not be found.')
            ]));
        }

        $tree = Validator::attributes($request)->tree();

        return Registry::responseFactory()->response(view($this->module->name() . '::modals/sosa-compute', [
            'tree'          => $tree,
            'xref'          => Validator::attributes($request)->isXref()->string('xref', '')
        ]));
    }
}
