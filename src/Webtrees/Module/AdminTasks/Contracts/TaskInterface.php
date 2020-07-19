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

namespace MyArtJaub\Webtrees\Module\AdminTasks\Contracts;

use MyArtJaub\Webtrees\Module\AdminTasks\Model\TaskSchedule;

/**
 * Interface for task to be run on a shedule
 */
interface TaskInterface
{
    /**
     * Display name of the task
     *
     * @return string
     */
    public function name(): string;
    
    /**
     * Return the default frequency for the execution of the task, in minutes.
     *
     * @return int Frequency for the execution of the task
     */
    public function defaultFrequency(): int;
    
    /**
     * Run the task's actions, and return whether the execution has been successful.
     *
     * @param TaskSchedule $task_schedule
     * @return bool Has the execution been a success
     */
    public function run(TaskSchedule $task_schedule): bool;
}
