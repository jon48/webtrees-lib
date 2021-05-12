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

use Fisharebest\Webtrees\FlashMessages;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Http\RequestHandlers\AbstractModuleComponentAction;
use Fisharebest\Webtrees\Services\ModuleService;
use Fisharebest\Webtrees\Services\TreeService;
use MyArtJaub\Webtrees\Contracts\Hooks\HookCollectorInterface;
use MyArtJaub\Webtrees\Module\Hooks\Services\HookService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Request handler for saving the configuration of the modules implementing hooks
 */
class ModulesHooksAction extends AbstractModuleComponentAction
{
    protected HookService $hook_service;

    /**
     * Constructor for ModulesHooksAction Request Handler
     *
     * @param ModuleService $module_service
     * @param TreeService $tree_service
     * @param HookService $hook_service
     */
    public function __construct(ModuleService $module_service, TreeService $tree_service, HookService $hook_service)
    {
        parent::__construct($module_service, $tree_service);
        $this->hook_service = $hook_service;
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Server\RequestHandlerInterface::handle()
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $hook_name = $request->getAttribute('hook_name');
        $hook_collector = $this->hook_service->find($hook_name, true);
        if ($hook_collector === null) {
            FlashMessages::addMessage(I18N::translate('The hook with name “%s” does not exist.', $hook_name), 'danger');
            return redirect(AdminConfigPage::class);
        }

        foreach ($hook_collector->hooks() as $hook) {
            $this->updateStatus(get_class($hook->module()), $request);
        }

        $this->updateHookOrder($hook_collector, $request);

        FlashMessages::addMessage(I18N::translate('The hook preferences have been updated.'), 'success');

        return redirect(route(ModulesHooksPage::class, ['hook_name' => $hook_name]));
    }

    /**
     * Update the order of modules for a hook interface.
     *
     * @template THook of \MyArtJaub\Webtrees\Contracts\Hooks\HookInterface
     * @param HookCollectorInterface<THook> $hook_collector
     * @param ServerRequestInterface $request
     */
    protected function updateHookOrder(HookCollectorInterface $hook_collector, ServerRequestInterface $request): void
    {
        $params = (array) $request->getParsedBody();

        $order = (array) ($params['order'] ?? []);
        $order = array_flip($order);

        foreach ($hook_collector->hooks() as $hook) {
            $this->hook_service->updateOrder($hook_collector, $hook->module(), $order[$hook->module()->name()] ?? 0);
        }
    }
}
