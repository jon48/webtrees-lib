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

use Fisharebest\Webtrees\Module\ModuleInterface;

/**
 * Interface for hook instances.
 */
interface HookInterface
{
    /**
     * Get the module attached to the hook
     *
     * @return ModuleInterface
     */
    public function module(): ModuleInterface;
}
