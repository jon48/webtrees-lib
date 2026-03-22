<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Hooks
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2011-2026, Jonathan Jaubart
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
    /** @var Collection<int, array<THook>> $hooks */
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

    #[\Override]
    public function module(): ModuleInterface
    {
        return $this->module;
    }

    #[\Override]
    public function name(): string
    {
        return $this->module->name() . '-' .
            mb_substr(str_replace('collector', '', mb_strtolower((new ReflectionClass($this))->getShortName())), 0, 64);
    }

    #[\Override]
    abstract public function title(): string;

    #[\Override]
    abstract public function description(): string;

    #[\Override]
    abstract public function hookInterface(): string;

    #[\Override]
    public function register(HookInterface $hook_instance, int $order): void
    {
        $this->hooks->put($order, array_merge($this->hooks->get($order, []), [$hook_instance]));
    }

    /**
     * @return Collection<THook>
     */
    #[\Override]
    public function hooks(): Collection
    {
        /** @var Collection<THook> */
        return $this->hooks->sortKeys()->flatten();
    }
}
