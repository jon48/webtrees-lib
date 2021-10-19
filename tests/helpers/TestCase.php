<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Certificates
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2021, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Tests\Helpers\Webtrees;

use Fisharebest\Webtrees\View;

/**
 * MyArtJaub base class for unit tests
 */
class TestCase extends \Fisharebest\Webtrees\TestCase
{
    /**
     * Register a view namespace to test views folder structure.
     *
     * @param string $namespace
     */
    public static function registerTestViewNamespace(string $namespace): void
    {
        View::registerNamespace($namespace, __DIR__ . '/../resources/views/' . $namespace . '/');
    }

    /**
     * Replace a view by the test default one file.
     *
     * @param string $view
     */
    public static function useDefaultViewFor(string $view): void
    {
        View::registerNamespace('maj-common', __DIR__ . '/../resources/views/common/');
        View::registerCustomView($view, 'maj-common::default');
    }
}
