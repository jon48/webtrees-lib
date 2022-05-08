<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Hooks
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2011-2022, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\Hooks\Http\RequestHandlers;

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\Http\Exceptions\HttpNotFoundException;
use MyArtJaub\Webtrees\Contracts\Hooks\HookInterface;
use MyArtJaub\Webtrees\Module\Hooks\Services\HookService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Request handler for displaying the configuration of the modules implementing hooks
 */
class ModulesHooksPage implements RequestHandlerInterface
{
    use ViewResponseTrait;

    protected HookService $hook_service;

    /**
     * Constructor for ModulesHooksPage request handler
     *
     * @param HookService $hook_service
     */
    public function __construct(HookService $hook_service)
    {
        $this->hook_service = $hook_service;
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Server\RequestHandlerInterface::handle()
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->layout = 'layouts/administration';

        $hook_name = Validator::attributes($request)->string('hook_name', '');
        $hook = $this->hook_service->find($hook_name, true);
        if ($hook === null) {
            throw new HttpNotFoundException(I18N::translate('The hook with name “%s” does not exist.', $hook_name));
        }

        $modules = $hook->hooks()
            ->sortKeys()
            ->mapWithKeys(fn(HookInterface $hook) => [$hook->module()->name() => $hook->module()]);

        return $this->viewResponse('admin/components', [
            'description'    => $hook->description(),
            'modules'        => $modules,
            'title'          => $hook->title(),
            'uses_access'    => false,
            'uses_sorting'   => true
        ]);
    }
}
