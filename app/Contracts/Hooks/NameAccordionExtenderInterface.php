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

namespace MyArtJaub\Webtrees\Contracts\Hooks;

use Fisharebest\Webtrees\Individual;

/**
 * Interface for hooks intending to extend the individual's names accordion.
 */
interface NameAccordionExtenderInterface extends HookInterface
{
    /**
     * Add a new card to the names accordion.
     *
     * @param Individual $individual
     * @return string
     */
    public function accordionCard(Individual $individual): string;
}
