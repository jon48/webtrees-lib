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

namespace MyArtJaub\Webtrees\Module\Hooks\Services;

use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Module\ModuleInterface;
use Fisharebest\Webtrees\Services\ModuleService;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Support\Collection;
use MyArtJaub\Webtrees\Contracts\Hooks\HookCollectorInterface;
use MyArtJaub\Webtrees\Contracts\Hooks\HookInterface;
use MyArtJaub\Webtrees\Contracts\Hooks\HookServiceInterface;
use MyArtJaub\Webtrees\Contracts\Hooks\ModuleHookSubscriberInterface;
use stdClass;

/**
 * Service for accessing hooks subscribed by modules.
 */
class HookService implements HookServiceInterface
{
    private ModuleService $module_service;

    /**
     * Constructor for HookService
     *
     * @param ModuleService $module_service
     */
    public function __construct(ModuleService $module_service)
    {
        $this->module_service = $module_service;
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\Hooks\HookServiceInterface::use()
     */
    public function use(string $hook_interface): ?HookCollectorInterface
    {
        return $this->all()->get($hook_interface);
    }


    /**
     * Find a hook collector by its name, with or without the disabled ones.
     *
     * @param string $hook_name
     * @return HookCollectorInterface|null
     */
    public function find(string $hook_name, bool $include_disabled = false): ?HookCollectorInterface
    {
        return $this->all($include_disabled)
            ->first(fn(HookCollectorInterface $hook_collector) => $hook_collector->name() === $hook_name);
    }

    /**
     * Get all hook collectors subscribed by modules, with hooks ordered, with or without the disabled ones.
     *
     * @param bool $include_disabled
     * @return Collection<string, HookCollectorInterface>
     */
    public function all(bool $include_disabled = false): Collection
    {
        return Registry::cache()->array()->remember('all-hooks', function () use ($include_disabled): Collection {
            $hooks_info = DB::table('maj_hook_order')
                ->get()
                ->groupBy(['majho_hook_name', 'majho_module_name']);

            $hooks = $this->module_service
                ->findByInterface(ModuleHookSubscriberInterface::class, $include_disabled)
                ->flatMap(fn(ModuleHookSubscriberInterface $module) => $module->listSubscribedHooks());

            $hook_collectors = collect();
            $hook_instances = collect();
            foreach ($hooks as $hook) {
                if (!($hook instanceof HookInterface)) {
                    continue;
                }
                if ($hook instanceof HookCollectorInterface) {
                    $hook_collectors->put($hook->hookInterface(), $hook);
                } else {
                    $hook_instances->add($hook);
                }
            }

            foreach ($hook_collectors as $hook_interface => $hook_collector) {
                $hook_info = $hooks_info->get($hook_collector->name()) ?? collect();
                foreach (
                    $hook_instances->filter(
                        fn(HookInterface $hook): bool => $hook instanceof $hook_interface
                    ) as $hook_instance
                ) {
                    $hook_module_info = $hook_info->get($hook_instance->module()->name(), collect())->first();
                    $hook_order = $hook_module_info instanceof stdClass ? (int) $hook_module_info->majho_hook_order : 0;
                    $hook_collector->register($hook_instance, $hook_order);
                }
            }
            return $hook_collectors;
        });
    }

    /**
     * Update the order of the modules implementing a hook in the database.
     *
     * @param HookCollectorInterface $hook_collector
     * @param ModuleInterface $module
     * @param int $order
     * @return int
     */
    public function updateOrder(HookCollectorInterface $hook_collector, ModuleInterface $module, int $order): int
    {
        return DB::table('maj_hook_order')
            ->upsert([
                'majho_module_name' =>  $module->name(),
                'majho_hook_name'   =>  $hook_collector->name(),
                'majho_hook_order'  =>  $order
            ], ['majho_module_name', 'majho_hook_name'], ['majho_hook_order']);
    }
}
