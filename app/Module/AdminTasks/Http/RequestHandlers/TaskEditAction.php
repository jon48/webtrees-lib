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

use Carbon\CarbonInterval;
use Fisharebest\Webtrees\FlashMessages;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Log;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Webtrees\Common\Tasks\TaskSchedule;
use MyArtJaub\Webtrees\Contracts\Tasks\ConfigurableTaskInterface;
use MyArtJaub\Webtrees\Module\AdminTasks\AdminTasksModule;
use MyArtJaub\Webtrees\Module\AdminTasks\Services\TaskScheduleService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

/**
 * Request handler for updating task schedules
 */
class TaskEditAction implements RequestHandlerInterface
{
    private ?AdminTasksModule $module;
    private TaskScheduleService $taskschedules_service;

    /**
     * Constructor for TaskEditAction Request Handler
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

        if ($task_schedule === null) {
            FlashMessages::addMessage(
                I18N::translate('The task shedule with ID “%s” does not exist.', I18N::number($task_sched_id)),
                'danger'
            );
            return redirect($admin_config_route);
        }

        $success = $this->updateGeneralSettings($task_schedule, $request);
        $success = $success && $this->updateSpecificSettings($task_schedule, $request);

        if ($success) {
            FlashMessages::addMessage(
                I18N::translate('The scheduled task has been successfully updated.'),
                'success'
            );
            //phpcs:ignore Generic.Files.LineLength.TooLong
            Log::addConfigurationLog('Module ' . $this->module->title() . ' : Task Schedule “' . $task_schedule->id() . '” has been updated.');
        }

        return redirect($admin_config_route);
    }

    /**
     * Update general settings for the task, based on the request parameters
     *
     * @param TaskSchedule $task_schedule
     * @param ServerRequestInterface $request
     * @return bool
     */
    private function updateGeneralSettings(TaskSchedule $task_schedule, ServerRequestInterface $request): bool
    {
        if ($this->module === null) {
            return false;
        }

        $frequency = Validator::parsedBody($request)->integer('frequency', 0);
        if ($frequency > 0) {
            $task_schedule->setFrequency($frequency);
        } else {
            FlashMessages::addMessage(I18N::translate('The frequency is not in a valid format.'), 'danger');
        }

        $is_limited = Validator::parsedBody($request)->boolean('is_limited', false);
        $nb_occur = Validator::parsedBody($request)->integer('nb_occur', 1);
        if ($is_limited) {
            if ($nb_occur > 0) {
                $task_schedule->setRemainingOccurrences($nb_occur);
            } else {
                FlashMessages::addMessage(
                    I18N::translate('The number of remaining occurrences is not in a valid format.'),
                    'danger'
                );
            }
        } else {
            $task_schedule->setRemainingOccurrences(0);
        }

        try {
            $this->taskschedules_service->update($task_schedule);
            return true;
        } catch (Throwable $ex) {
            Log::addErrorLog(
                sprintf(
                    'Error while updating the Task Schedule "%s". Exception: %s',
                    $task_schedule->id(),
                    $ex->getMessage()
                )
            );
        }

        FlashMessages::addMessage(I18N::translate('An error occured while updating the scheduled task.'), 'danger');
        //@phpcs:ignore Generic.Files.LineLength.TooLong
        Log::addConfigurationLog('Module ' . $this->module->title() . ' : Task Schedule “' . $task_schedule->id() . '” could not be updated. See error log.');
        return false;
    }

    /**
     * Update general settings for the task, based on the request parameters
     *
     * @param TaskSchedule $task_schedule
     * @param ServerRequestInterface $request
     * @return bool
     */
    private function updateSpecificSettings(TaskSchedule $task_schedule, ServerRequestInterface $request): bool
    {
        if ($this->module === null) {
            return false;
        }

        $task = $this->taskschedules_service->findTask($task_schedule->taskId());
        if ($task === null || !($task instanceof ConfigurableTaskInterface)) {
            return true;
        }

        /** @var \MyArtJaub\Webtrees\Contracts\Tasks\TaskInterface&\MyArtJaub\Webtrees\Contracts\Tasks\ConfigurableTaskInterface $task */
        if (!$task->updateConfig($request, $task_schedule)) {
            FlashMessages::addMessage(
                I18N::translate(
                    'An error occured while updating the specific settings of administrative task “%s”.',
                    $task->name()
                ),
                'danger'
            );
            //phpcs:ignore Generic.Files.LineLength.TooLong
            Log::addConfigurationLog('Module ' . $this->module->title() . ' : AdminTask “' . $task->name() . '” specific settings could not be updated. See error log.');
        }

        return true;
    }
}
