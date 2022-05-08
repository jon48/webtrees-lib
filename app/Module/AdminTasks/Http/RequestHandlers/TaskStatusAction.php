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

namespace MyArtJaub\Webtrees\Module\AdminTasks\Http\RequestHandlers;

use Fisharebest\Webtrees\FlashMessages;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Log;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Webtrees\Module\AdminTasks\AdminTasksModule;
use MyArtJaub\Webtrees\Module\AdminTasks\Services\TaskScheduleService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Request handler for updating the status of task schedules
 */
class TaskStatusAction implements RequestHandlerInterface
{
    private ?AdminTasksModule $module;
    private TaskScheduleService $taskschedules_service;

    /**
     * Constructor for TaskStatusAction Request Handler
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
        $admin_config_route = route(AdminConfigPage::class);

        if ($this->module === null) {
            FlashMessages::addMessage(
                I18N::translate('The attached module could not be found.'),
                'danger'
            );
            return redirect($admin_config_route);
        }

        $task_sched_id = Validator::attributes($request)->integer('task', -1);
        $task_schedule = $this->taskschedules_service->find($task_sched_id);

        $admin_config_route = route(AdminConfigPage::class);

        if ($task_schedule === null) {
            FlashMessages::addMessage(
                I18N::translate('The task shedule with ID “%s” does not exist.', I18N::number($task_sched_id)),
                'danger'
            );
            return redirect($admin_config_route);
        }

        Validator::attributes($request)->boolean('enable', false) ?
            $task_schedule->enable() :
            $task_schedule->disable();

        if ($this->taskschedules_service->update($task_schedule) > 0) {
            FlashMessages::addMessage(
                I18N::translate('The scheduled task has been successfully updated.'),
                'success'
            );
            //phpcs:ignore Generic.Files.LineLength.TooLong
            Log::addConfigurationLog('Module ' . $this->module->title() . ' : Task Schedule “' . $task_schedule->id() . '” has been updated.');
        } else {
            FlashMessages::addMessage(
                I18N::translate('An error occured while updating the scheduled task.'),
                'danger'
            );
            //phpcs:ignore Generic.Files.LineLength.TooLong
            Log::addConfigurationLog('Module ' . $this->module->title() . ' : Task Schedule “' . $task_schedule->id() . '” could not be updated. See error log.');
        }

        return redirect($admin_config_route);
    }
}
