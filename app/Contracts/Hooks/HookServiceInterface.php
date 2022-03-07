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

/**
 * Interface for services providing access to the hooks.
 */
interface HookServiceInterface
{
    /**
     * Select the hook collector to use for a specific hook interface
     *
     * @template THook of HookInterface
     * @param class-string<THook> $hook_interface
     * @return HookCollectorInterface|NULL
     */
    public function use(string $hook_interface): ?HookCollectorInterface;
}
