<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage AdminTasks
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2012-2020, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Contracts\Tasks;

use MyArtJaub\Webtrees\Common\Tasks\TaskSchedule;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface for tasks requiring a specific configuration
 */
interface ConfigurableTaskInterface
{
    /**
     * Returns the HTML code to display the specific task configuration.
     *
     * @param ServerRequestInterface $request
     * @return string HTML code
     */
    public function configView(ServerRequestInterface $request): string;

    /**
     * Update the specific configuration of the task.
     *
     * @param ServerRequestInterface $request
     * @param TaskSchedule $task_schedule
     * @return bool Result of the update
     */
    public function updateConfig(ServerRequestInterface $request, TaskSchedule $task_schedule): bool;
}
