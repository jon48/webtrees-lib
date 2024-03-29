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

/**
 * Inferface for modules providing place mappers.
 */
interface ModulePlaceMapperProviderInterface
{
    /**
     * List place mappers provided by the module as an array.
     *
     * @return string[] List of place mappers
     */
    public function listPlaceMappers(): array;
}
