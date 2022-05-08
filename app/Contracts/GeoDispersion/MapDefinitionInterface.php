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
 * Interface for GeoJson map definitions
 */
interface MapDefinitionInterface
{
    /**
     * Get the map ID
     *
     * @return string
     */
    public function id(): string;

    /**
     * Get the map title
     *
     * @return string
     */
    public function title(): string;

    /**
     * Get the features in the map
     *
     * @return \Brick\Geo\IO\GeoJSON\Feature[]
     */
    public function features(): array;
}
