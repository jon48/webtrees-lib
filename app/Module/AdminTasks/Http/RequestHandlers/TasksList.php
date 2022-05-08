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

namespace MyArtJaub\Webtrees\Module\AdminTasks\Http\RequestHandlers;

use Carbon\CarbonInterval;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Http\Exceptions\HttpNotFoundException;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Webtrees\Common\Tasks\TaskSchedule;
use MyArtJaub\Webtrees\Module\AdminTasks\AdminTasksModule;
use MyArtJaub\Webtrees\Module\AdminTasks\Services\TaskScheduleService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Request handler for listing task schedules
 *
 */
class TasksList implements RequestHandlerInterface
{
    private ?AdminTasksModule $module;
    private TaskScheduleService $taskschedules_service;

    /**
     * Constructor for TasksList Request Handler
     *
     * @param ModuleService $module_service
     * @param TaskScheduleService $taskschedules_service
     */
    public function __construct(
        ModuleService $module_service,
        TaskScheduleService $taskschedules_service
    ) {
        $this->module = $module_service->findByInterface(AdminTasksModule::class)->first();
        $this->taskschedules_service = $taskschedules_service;
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Server\RequestHandlerInterface::handle()
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->module === null) {
            throw new HttpNotFoundException(I18N::translate('The attached module could not be found.'));
        }

        $module = $this->module;
        $module_name = $this->module->name();
        return response(['data' => $this->taskschedules_service->all(true, true)
            ->map(function (TaskSchedule $schedule) use ($module, $module_name): array {
                $task = $this->taskschedules_service->findTask($schedule->taskId());
                $task_name = $task !== null ? $task->name() : I18N::translate('Task not found');

                return [
                    'edit' =>   view($module_name . '::admin/tasks-table-options', [
                        'task_sched_id' => $schedule->id(),
                        'task_sched_enabled' => $schedule->isEnabled(),
                        'task_edit_route' => route(TaskEditPage::class, ['task' => $schedule->id()]),
                        'task_status_route' => route(TaskStatusAction::class, [
                            'task' => $schedule->id(),
                            'enable' => $schedule->isEnabled() ? 0 : 1
                        ])
                    ]),
                    'status'    =>  [
                        'display'   =>  view($module_name . '::components/yes-no-icons', [
                            'yes' => $schedule->isEnabled()
                        ]),
                        'raw'       =>  $schedule->isEnabled() ? 1 : 0
                    ],
                    'task_name' =>  [
                        'display'   =>  '<bdi>' . e($task_name) . '</bdi>',
                        'raw'       =>  $task_name
                    ],
                    'last_run'  =>  [
                        'display'   =>  $schedule->lastRunTime()->timestamp() === 0 ?
                            view('components/datetime', ['timestamp' => $schedule->lastRunTime()]) :
                            view('components/datetime-diff', ['timestamp' => $schedule->lastRunTime()]),
                        'raw'       =>  $schedule->lastRunTime()->timestamp()
                    ],
                    'last_result'   =>  [
                        'display'   => view($module_name . '::components/yes-no-icons', [
                            'yes' => $schedule->wasLastRunSuccess()
                        ]),
                        'raw'       =>  $schedule->wasLastRunSuccess() ? 1 : 0
                    ],
                    'frequency' =>
                        '<bdi>' . e(CarbonInterval::minutes($schedule->frequency())->cascade()->forHumans()) . '</bdi>',
                    'nb_occurrences'    =>  $schedule->remainingOccurrences() > 0 ?
                        I18N::number($schedule->remainingOccurrences()) :
                        I18N::translate('Unlimited'),
                    'running'   =>  view($module_name . '::components/yes-no-icons', [
                        'yes' => $schedule->isRunning(),
                        'text_yes' => I18N::translate('Running'),
                        'text_no' => I18N::translate('Not running')
                    ]),
                    'run'       =>  view($module_name . '::admin/tasks-table-run', [
                        'task_sched_id' => $schedule->id(),
                        'run_route' => route(TaskTrigger::class, [
                            'task'  =>  $schedule->taskId(),
                            'force' =>  $module->getPreference('MAJ_AT_FORCE_EXEC_TOKEN')
                        ])
                    ])
                ];
            })
        ]);
    }
}
