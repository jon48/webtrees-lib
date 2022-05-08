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

namespace MyArtJaub\Webtrees\Common\Hooks;

use Fisharebest\Webtrees\Module\ModuleInterface;
use Illuminate\Support\Collection;
use MyArtJaub\Webtrees\Contracts\Hooks\HookCollectorInterface;
use MyArtJaub\Webtrees\Contracts\Hooks\HookInterface;
use ReflectionClass;

/**
 * Abstract calss for hooks collectors.
 *
 * @template THook of HookInterface
 */
abstract class AbstractHookCollector implements HookCollectorInterface, HookInterface
{
    /** @var Collection<THook> $hooks */
    protected Collection $hooks;

    private ModuleInterface $module;

    /**
     * Constructor for AbstractHookCollector
     *
     * @param ModuleInterface $module
     */
    public function __construct(ModuleInterface $module)
    {
        $this->hooks = new Collection();
        $this->module = $module;
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\Hooks\HookInterface::module()
     */
    public function module(): ModuleInterface
    {
        return $this->module;
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\Hooks\HookCollectorInterface::name()
     */
    public function name(): string
    {
        return $this->module->name() . '-' .
            mb_substr(str_replace('collector', '', mb_strtolower((new ReflectionClass($this))->getShortName())), 0, 64);
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\Hooks\HookCollectorInterface::title()
     */
    abstract public function title(): string;

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\Hooks\HookCollectorInterface::description()
     */
    abstract public function description(): string;

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\Hooks\HookCollectorInterface::hookInterface()
     */
    abstract public function hookInterface(): string;

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\Hooks\HookCollectorInterface::register()
     */
    public function register(HookInterface $hook_instance, int $order): void
    {
        if ($this->hooks->has($order)) {
            $this->hooks->splice($order + 1, 0, [$hook_instance]);
        } else {
            $this->hooks->put($order, $hook_instance);
        }
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\Hooks\HookCollectorInterface::hooks()
     *
     * @return Collection<THook>
     */
    public function hooks(): Collection
    {
        return $this->hooks->sortKeys();
    }
}
