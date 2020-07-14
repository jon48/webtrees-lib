<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage AdminTasks
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2020, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\AdminTasks\Services;

use Carbon\CarbonInterval;
use Fisharebest\Webtrees\Carbon;
use Fisharebest\Webtrees\Services\ModuleService;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use MyArtJaub\Webtrees\Module\AdminTasks\Model\ModuleTasksProviderInterface;
use MyArtJaub\Webtrees\Module\AdminTasks\Model\TaskInterface;
use MyArtJaub\Webtrees\Module\AdminTasks\Model\TaskSchedule;
use Closure;
use Exception;
use stdClass;

/**
 * Service for Task Schedules CRUD, and tasks execution
 *
 */
class TaskScheduleService
{
    /**
     * Time-out after which the task will be considered not running any more.
     * In seconds, default 5 mins.
     * @var integer
     */
    public const TASK_TIME_OUT = 600;
    
    /**
     * @var Collection $available_tasks
     */
    private $available_tasks;
    
    /**
     * Returns all Tasks schedules in database.
     * Stored records can be synchronised with the tasks actually available to the system.
     *
     * @param bool $sync_available Should tasks synchronised with available ones
     * @param bool $include_disabled Should disabled tasks be returned
     * @return Collection Collection of TaskSchedule
     */
    public function all(bool $sync_available = false, bool $include_disabled = true): Collection
    {
        $tasks_schedules = DB::table('maj_admintasks')
        ->select()
        ->get()
        ->map(self::rowMapper());
        
        if ($sync_available) {
            $available_tasks = clone $this->available();
            foreach ($tasks_schedules as $task_schedule) {
                /** @var TaskSchedule $task_schedule */
                if ($available_tasks->has($task_schedule->taskId())) {
                    $available_tasks->forget($task_schedule->taskId());
                } else {
                    $this->delete($task_schedule);
                }
            }
            
            foreach ($available_tasks as $task_name => $task) {
                /** @var TaskInterface $task */
                $this->insertTask($task_name, $task->defaultFrequency());
            }
            
            return $this->all(false, $include_disabled);
        }
        
        return $tasks_schedules;
    }
    
    /**
     * Returns tasks exposed through modules implementing ModuleTasksProviderInterface.
     *
     * @return Collection
     */
    public function available(): Collection
    {
        if ($this->available_tasks === null) {
            $tasks_providers = app(ModuleService::class)->findByInterface(ModuleTasksProviderInterface::class);
            
            $this->available_tasks = new Collection();
            foreach ($tasks_providers as $task_provider) {
                $this->available_tasks = $this->available_tasks->merge($task_provider->listTasks());
            }
        }
        return $this->available_tasks;
    }
    
    /**
     * Find a task schedule by its ID.
     *
     * @param int $task_schedule_id
     * @return TaskSchedule|NULL
     */
    public function find(int $task_schedule_id): ?TaskSchedule
    {
        return DB::table('maj_admintasks')
            ->select()
            ->where('majat_id', '=', $task_schedule_id)
            ->get()
            ->map(self::rowMapper())
            ->first();
    }
    
    /**
     * Add a new task schedule with the specified task ID, and frequency if defined.
     * Uses default for other settings.
     *
     * @param string $task_id
     * @param int $frequency
     * @return bool
     */
    public function insertTask(string $task_id, int $frequency = 0): bool
    {
        $values = ['majat_task_id' => $task_id];
        if ($frequency > 0) {
            $values['majat_frequency'] = $frequency;
        }
        
        return DB::table('maj_admintasks')
            ->insert($values);
    }
    
    /**
     * Update a task schedule.
     * Returns the number of tasks schedules updated.
     *
     * @param TaskSchedule $task_schedule
     * @return int
     */
    public function update(TaskSchedule $task_schedule): int
    {
        return DB::table('maj_admintasks')
            ->where('majat_id', '=', $task_schedule->id())
            ->update([
                'majat_status'      =>  $task_schedule->isEnabled() ? 'enabled' : 'disabled',
                'majat_last_run'    =>  $task_schedule->lastRunTime(),
                'majat_last_result' =>  $task_schedule->wasLastRunSuccess(),
                'majat_frequency'   =>  $task_schedule->frequency()->totalMinutes,
                'majat_nb_occur'    =>  $task_schedule->remainingOccurences(),
                'majat_running'     =>  $task_schedule->isRunning()
            ]);
    }
    
    /**
     * Delete a task schedule.
     *
     * @param TaskSchedule $task_schedule
     * @return int
     */
    public function delete(TaskSchedule $task_schedule): int
    {
        return DB::table('maj_admintasks')
            ->where('majat_id', '=', $task_schedule->id())
            ->delete();
    }
    
    /**
     * Find a task by its name
     *
     * @param string $task_id
     * @return TaskInterface|NULL
     */
    public function findTask(string $task_id): ?TaskInterface
    {
        if ($this->available()->has($task_id)) {
            return app($this->available()->get($task_id));
        }
        return null;
    }
    
    /**
     * Retrieve all tasks that are candidates to be run.
     *
     * @param bool $force Should the run be forced
     * @param string $task_id Specific task ID to be run
     * @return Collection
     */
    public function findTasksToRun(bool $force, string $task_id = null): Collection
    {
        $query = DB::table('maj_admintasks')
            ->select()
            ->where('majat_status', '=', 'enabled')
            ->where(function (Builder $query) {

                $query->where('majat_running', '=', 0)
                ->orWhere('majat_last_run', '<=', Carbon::now()->subSeconds(self::TASK_TIME_OUT));
            });
            
        if (!$force) {
            $query->where(function (Builder $query) {

                $query->where('majat_running', '=', 0)
                    ->orWhereRaw('DATE_ADD(majat_last_run, INTERVAL majat_frequency MINUTE) <= NOW()');
            });
        }
        
        if ($task_id !== null) {
            $query->where('majat_task_id', '=', $task_id);
        }
        
        return $query->get()->map(self::rowMapper());
    }
    
    /**
     * Run the task associated with the schedule.
     * The task will run if either forced to, or its next scheduled run time has been exceeded.
     * The last run time is recorded only if the task is successful.
     *
     * @param TaskSchedule $task_schedule
     * @param boolean $force
     */
    public function run(TaskSchedule $task_schedule, $force = false): void
    {
        /** @var TaskSchedule $task_schedule */
        $task_schedule = DB::table('maj_admintasks')
            ->select()
            ->where('majat_id', '=', $task_schedule->id())
            ->lockForUpdate()
            ->get()
            ->map(self::rowMapper())
            ->first();
        
        if (
            !$task_schedule->isRunning() &&
            ($force || $task_schedule->lastRunTime()->add($task_schedule->frequency())->lessThan(Carbon::now())) &&
            $task_schedule->setLastResult(false) &&  // @phpstan-ignore-line  Used as setter, not as a condition
            $task = $this->findTask($task_schedule->taskId())
        ) {
            $task_schedule->startRunning();
            $this->update($task_schedule);
            
            try {
                $task_schedule->setLastResult($task->run($task_schedule));
            } catch (Exception $ex) {
            }
            
            if ($task_schedule->wasLastRunSuccess()) {
                $task_schedule->setLastRunTime(Carbon::now());
                $task_schedule->decrementRemainingOccurences();
            }
            $task_schedule->stopRunning();
            $this->update($task_schedule);
        } else {
            $this->update($task_schedule);
        }
    }

    /**
     * Mapper to return a TaskSchedule object from an object.
     *
     * @return Closure
     */
    public static function rowMapper(): Closure
    {
        return static function (stdClass $row): TaskSchedule {

            return new TaskSchedule(
                (int) $row->majat_id,
                $row->majat_task_id,
                $row->majat_status === 'enabled',
                Carbon::parse($row->majat_last_run),
                (bool) $row->majat_last_result,
                CarbonInterval::minutes($row->majat_frequency),
                (int) $row->majat_nb_occur,
                (bool) $row->majat_running
            );
        };
    }
}
