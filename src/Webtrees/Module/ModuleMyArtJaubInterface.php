<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2020, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module;

use Aura\Router\Map;
use Fisharebest\Webtrees\Module\ModuleCustomInterface;

/**
 * MyArtJaub Module Interface
 */
interface ModuleMyArtJaubInterface extends ModuleCustomInterface
{
    /**
     * Add module routes to webtrees route loader
     *
     * @param Map $router
     */
    public function loadRoutes(Map $router): void;
/**
     * Returns the URL of the module specific stylesheets.
     * It will look for a CSS file matching the theme name (e.g. xenea.min.css),
     * and fallback to default.min.css if none are found
     *
     * @return string
     */
    public function moduleCssUrl(): string;
}
