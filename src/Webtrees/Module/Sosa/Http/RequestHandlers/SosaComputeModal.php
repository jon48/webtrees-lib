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
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Services\ModuleService;
use Fisharebest\Webtrees\Services\UserService;
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
     * @var SosaModule $module
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
            return response(
                I18N::translate('The attached module could not be found.'),
                StatusCodeInterface::STATUS_NOT_FOUND
            );
        }

        $tree = $request->getAttribute('tree');
        assert($tree instanceof Tree);

        return response(view($this->module->name() . '::modals/sosa-compute', [
            'tree'          => $tree,
            'xref'          =>  $request->getAttribute('xref')
        ]));
    }
}
