<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage AdminTasks
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2012-2016, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Common\Tasks;

use Carbon\CarbonInterval;
use Fisharebest\Webtrees\Carbon;

/**
 * Object to describe a schedule for a task.
 * Setters can be chained.
 *
 */
class TaskSchedule
{
    private int $id;
    private bool $enabled;
    private string $task_id;
    private Carbon $last_run;
    private bool $last_result;
    private CarbonInterval $frequency;
    private int $nb_occurrences;
    private bool $is_running;

    /**
     * Constructor for TaskSchedule
     *
     * @param int $id Schedule ID
     * @param string $task_id Task ID
     * @param bool $enabled Is the schedule enabled
     * @param Carbon $last_run Last successful run date/time
     * @param bool $last_result Result of the last run
     * @param CarbonInterval $frequency Schedule frequency
     * @param int $nb_occurrences Number of remaining occurrences to be run
     * @param bool $is_running Is the task currently running
     */
    public function __construct(
        int $id,
        string $task_id,
        bool $enabled,
        Carbon $last_run,
        bool $last_result,
        CarbonInterval $frequency,
        int $nb_occurrences,
        bool $is_running
    ) {
        $this->id = $id;
        $this->task_id = $task_id;
        $this->enabled = $enabled;
        $this->last_run = $last_run;
        $this->last_result = $last_result;
        $this->frequency = $frequency;
        $this->nb_occurrences = $nb_occurrences;
        $this->is_running = $is_running;
    }

    /**
     * Get the schedule ID.
     *
     * @return int
     */
    public function id(): int
    {
        return $this->id;
    }

    /**
     * Get the task ID.
     *
     * @return string
     */
    public function taskId(): string
    {
        return $this->task_id;
    }

    /**
     * Returns whether the schedule is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Enable the schedule
     *
     * @return $this
     */
    public function enable(): self
    {
        $this->enabled = true;
        return $this;
    }

    /**
     * Disable the schedule
     *
     * @return $this
     */
    public function disable(): self
    {
        $this->enabled = false;
        return $this;
    }

    /**
     * Get the frequency of the schedule
     *
     * @return CarbonInterval
     */
    public function frequency(): CarbonInterval
    {
        return $this->frequency;
    }

    /**
     * Set the frequency of the schedule
     *
     * @param CarbonInterval $frequency
     * @return $this
     */
    public function setFrequency(CarbonInterval $frequency): self
    {
        $this->frequency = $frequency;
        return $this;
    }

    /**
     * Get the date/time of the last successful run.
     *
     * @return Carbon
     */
    public function lastRunTime(): Carbon
    {
        return $this->last_run;
    }

    /**
     * Set the last successful run date/time
     *
     * @param Carbon $last_run
     * @return $this
     */
    public function setLastRunTime(Carbon $last_run): self
    {
        $this->last_run = $last_run;
        return $this;
    }

    /**
     * Returns whether the last run was successful
     *
     * @return bool
     */
    public function wasLastRunSuccess(): bool
    {
        return $this->last_result;
    }

    /**
     * Set the last run result
     *
     * @param bool $last_result
     * @return $this
     */
    public function setLastResult(bool $last_result): self
    {
        $this->last_result = $last_result;
        return $this;
    }

    /**
     * Get the number of remaining of occurrences of task runs.
     * Returns 0 if the tasks must be run indefinitely.
     *
     * @return int
     */
    public function remainingOccurences(): int
    {
        return $this->nb_occurrences;
    }

    /**
     * Decrements the number of remaining occurences by 1.
     * The task will be disabled when the number reaches 0.
     *
     * @return $this
     */
    public function decrementRemainingOccurences(): self
    {
        if ($this->nb_occurrences > 0) {
            $this->nb_occurrences--;
            if ($this->nb_occurrences == 0) {
                $this->disable();
            }
        }
        return $this;
    }

    /**
     * Set the number of remaining occurences of task runs.
     *
     * @param int $nb_occurrences
     * @return $this
     */
    public function setRemainingOccurences(int $nb_occurrences): self
    {
        $this->nb_occurrences = $nb_occurrences;
        return $this;
    }

    /**
     * Returns whether the task is running
     * @return bool
     */
    public function isRunning(): bool
    {
        return $this->is_running;
    }

    /**
     * Informs the schedule that the task is going to run
     *
     * @return $this
     */
    public function startRunning(): self
    {
        $this->is_running = true;
        return $this;
    }

    /**
     * Informs the schedule that the task has stopped running.
     * @return $this
     */
    public function stopRunning(): self
    {
        $this->is_running = false;
        return $this;
    }

    /**
     * Returns the schedule details as an associate array
     *
     * @phpcs:ignore Generic.Files.LineLength.TooLong
     * @return array{id: int, task_id: string, enabled: bool, last_run: Carbon, last_result: bool, frequency: CarbonInterval, nb_occurrences: int, is_running: bool}
     */
    public function toArray(): array
    {
        return [
            'id'            =>  $this->id,
            'task_id'       =>  $this->task_id,
            'enabled'       =>  $this->enabled,
            'last_run'      =>  $this->last_run,
            'last_result'   =>  $this->last_result,
            'frequency'     =>  $this->frequency,
            'nb_occurrences' =>  $this->nb_occurrences,
            'is_running'    =>  $this->is_running
        ];
    }
}
