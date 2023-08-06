<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage AdminTasks
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2020-2023, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Contracts\Tasks;

use Fisharebest\Webtrees\Module\ModuleInterface;

/**
 * Inferface for modules exposing tasks to be run on a schedule
 */
interface ModuleTasksProviderInterface extends ModuleInterface
{
    /**
     * List tasks provided by the module as an associative array.
     * They keys are used as task IDs for storage and reference.
     *
     * @return array<string, string> List of tasks
     */
    public function listTasks(): array;
}
