<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 * Helper functions to be used in the core webtrees application.
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Helpers
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2021, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use MyArtJaub\Webtrees\Contracts\Hooks\HookServiceInterface;

/**
 * Invoke a hook interface and get hold of its result.
 *
 * @template THook of \MyArtJaub\Webtrees\Contracts\Hooks\HookInterface
 * @template TReturn
 *
 * @param class-string<THook> $hook_interface
 * @param callable(\MyArtJaub\Webtrees\Contracts\Hooks\HookCollectorInterface): TReturn $apply
 * @param TReturn|null $default
 * @return TReturn|null
 */
function hook(string $hook_interface, callable $apply, $default = null)
{
    try {
        $hook_collector = app(HookServiceInterface::class)->use($hook_interface);
        if ($hook_collector !== null) {
            return $apply($hook_collector);
        }
    } catch (BindingResolutionException $ex) {
    }

    return $default;
}

/**
 * Get the updated column index after insertion of new columns.
 *
 * @param int $initial_index
 * @param Collection<int> $new_column_indexes
 * @return int
 */
function columnIndex(int $initial_index, Collection $new_column_indexes): int
{
    return $initial_index + $new_column_indexes->filter(fn(int $i) => $i <= $initial_index)->count();
}
