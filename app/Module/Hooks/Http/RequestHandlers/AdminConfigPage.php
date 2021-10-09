<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Hooks
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2011-2021, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\Hooks\Http\RequestHandlers;

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Http\Exceptions\HttpNotFoundException;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Webtrees\Module\Hooks\HooksModule;
use MyArtJaub\Webtrees\Module\Hooks\Services\HookService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Request handler for displaying configuration of the module
 */
class AdminConfigPage implements RequestHandlerInterface
{
    use ViewResponseTrait;

    private ?HooksModule $module;
    private HookService $hook_service;

    /**
     * Constructor for AdminConfigPage Request Handler
     *
     * @param ModuleService $module_service
     * @param HookService $hook_service
     */
    public function __construct(ModuleService $module_service, HookService $hook_service)
    {
        $this->module = $module_service->findByInterface(HooksModule::class)->first();
        $this->hook_service = $hook_service;
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Server\RequestHandlerInterface::handle()
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->layout = 'layouts/administration';

        if ($this->module === null) {
            throw new HttpNotFoundException(I18N::translate('The attached module could not be found.'));
        }

        return $this->viewResponse($this->module->name() . '::admin/config', [
            'title'                 =>  $this->module->title(),
            'hook_interfaces_list'  =>  $this->hook_service->all(true)
        ]);
    }
}
