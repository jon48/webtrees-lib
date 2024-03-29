<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage AdminTasks
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2020-2022, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\AdminTasks\Http\RequestHandlers;

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\Http\Exceptions\HttpNotFoundException;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Webtrees\Contracts\Tasks\ConfigurableTaskInterface;
use MyArtJaub\Webtrees\Module\AdminTasks\AdminTasksModule;
use MyArtJaub\Webtrees\Module\AdminTasks\Services\TaskScheduleService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Request handler for editing task schedules
 */
class TaskEditPage implements RequestHandlerInterface
{
    use ViewResponseTrait;

    private ?AdminTasksModule $module;
    private TaskScheduleService $taskschedules_service;

    /**
     * Constructor for TaskEditPage Request Handler
     *
     * @param ModuleService $module_service
     * @param TaskScheduleService $taskschedules_service
     */
    public function __construct(ModuleService $module_service, TaskScheduleService $taskschedules_service)
    {
        $this->module = $module_service->findByInterface(AdminTasksModule::class)->first();
        $this->taskschedules_service = $taskschedules_service;
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Server\RequestHandlerInterface::handle()
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->layout = 'layouts/administration';

        if ($this->module === null) {
            throw new HttpNotFoundException(I18N::translate('The attached module could not be found.'));
        }

        $task_sched_id = Validator::attributes($request)->integer('task', -1);
        $task_schedule = $this->taskschedules_service->find($task_sched_id);

        if ($task_schedule === null) {
            throw new HttpNotFoundException(I18N::translate('The Task schedule could not be found.'));
        }

        $task = $this->taskschedules_service->findTask($task_schedule->taskId());

        if ($task === null) {
            throw new HttpNotFoundException(I18N::translate('The Task schedule could not be found.'));
        }

        $has_task_config = $task instanceof ConfigurableTaskInterface;
        /** @var \MyArtJaub\Webtrees\Contracts\Tasks\TaskInterface&\MyArtJaub\Webtrees\Contracts\Tasks\ConfigurableTaskInterface $task */

        return $this->viewResponse($this->module->name() . '::admin/tasks-edit', [
            'module'            =>  $this->module,
            'title'             =>  I18N::translate('Edit the administrative task') . ' - ' . $task->name(),
            'task_schedule'     =>  $task_schedule,
            'task'              =>  $task,
            'has_task_config'   =>  $has_task_config,
            'task_config_view'  =>  $has_task_config ? $task->configView($request) : ''
        ]);
    }
}
