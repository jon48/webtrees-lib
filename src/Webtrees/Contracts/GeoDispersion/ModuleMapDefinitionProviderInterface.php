<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage GeoDispersion
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2021, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Contracts\GeoDispersion;

/**
 * Inferface for modules providing map definitions.
 */
interface ModuleMapDefinitionProviderInterface
{
    /**
     * List map definitions provided by the module as an array.
     *
     * @return MapDefinitionInterface[] List of map definitions
     */
    public function listMapDefinition(): array;
}
