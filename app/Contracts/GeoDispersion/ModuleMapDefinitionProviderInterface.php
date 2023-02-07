<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage GeoDispersion
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2021-2022, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Contracts\GeoDispersion;

use Fisharebest\Webtrees\Module\ModuleInterface;

/**
 * Inferface for modules providing map definitions.
 */
interface ModuleMapDefinitionProviderInterface extends ModuleInterface
{
    /**
     * List map definitions provided by the module as an array.
     *
     * @return array<int, MapDefinitionInterface> List of map definitions
     */
    public function listMapDefinition(): array;
}
