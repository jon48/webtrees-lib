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

namespace MyArtJaub\Webtrees\Contracts\Hooks;

use Illuminate\Support\Collection;

/**
 * Interface for collating hooks of a same interface.
 */
interface HookCollectorInterface
{
    /**
     * Get the unique internal name for the hook collector
     *
     * @return string
     */
    public function name(): string;

    /**
     * Get the title to be displayed to idenfity the hook collector
     *
     * @return string
     */
    public function title(): string;

    /**
     * Get a short description for the hook collector
     *
     * @return string
     */
    public function description(): string;

    /**
     * Get the interface collated by the hook collector
     *
     * @return class-string
     */
    public function hookInterface(): string;

    /**
     * Register a hook instance in the hook collector
     *
     * @param HookInterface $hook_instance
     * @param int $order
     */
    public function register(HookInterface $hook_instance, int $order): void;

    /**
     * Get the list of hooks registered against the hook collector
     *
     * @return Collection<HookInterface>
     */
    public function hooks(): Collection;
}
