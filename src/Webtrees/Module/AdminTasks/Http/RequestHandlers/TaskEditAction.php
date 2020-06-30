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

use Carbon\CarbonInterval;
use Fisharebest\Webtrees\FlashMessages;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Log;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Webtrees\Module\AdminTasks\AdminTasksModule;
use MyArtJaub\Webtrees\Module\AdminTasks\Model\ConfigurableTaskInterface;
use MyArtJaub\Webtrees\Module\AdminTasks\Model\TaskSchedule;
use MyArtJaub\Webtrees\Module\AdminTasks\Services\TaskScheduleService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Exception;

/**
 * Request handler for updating task schedules
 */
class TaskEditAction implements RequestHandlerInterface
{
    /**
     * @var AdminTasksModule $module
     */
    private $module;
    
    /**
     * @var TaskScheduleService $taskschedules_service
     */
    private $taskschedules_service;
    
    /**
     * Constructor for TaskEditAction Request Handler
     * 
     * @param ModuleService $module_service
     * @param TaskScheduleService $taskschedules_service
     */
    function __construct(
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
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        $task_sched_id = (int) $request->getAttribute('task');
        $task_schedule = $this->taskschedules_service->find($task_sched_id);
        
        $admin_config_route = route(AdminConfigPage::class);
        
        if($task_schedule === null) {
            FlashMessages::addMessage(I18N::translate('The task shedule with ID “%s” does not exist.', e($task_sched_id)), 'danger');
            return redirect($admin_config_route);
        }
        
        $success = $this->updateGeneralSettings($task_schedule, $request);
        $success = $success && $this->updateSpecificSettings($task_schedule, $request);
        
        if($success) {
            FlashMessages::addMessage(I18N::translate('The scheduled task has been successfully updated', 'success'));
            Log::addConfigurationLog('Module '.$this->module->title().' : Task Schedule “'. $task_schedule->id() .'” has been updated.');
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
    private function updateGeneralSettings(TaskSchedule $task_schedule, ServerRequestInterface $request) : bool
    {
        $params = (array) $request->getParsedBody();
        
        $frequency = (int) $params['frequency'];
        if($frequency > 0) {
            $task_schedule->setFrequency(CarbonInterval::minutes($frequency));
        }
        else {
            FlashMessages::addMessage(I18N::translate('The frequency is not in a valid format'), 'danger');
        }
        
        $is_limited = (bool) $params['is_limited'];
        $nb_occur = (int) $params['nb_occur'];
        
        if($is_limited) {
            if($nb_occur > 0) {
                $task_schedule->setRemainingOccurences($nb_occur);
            }
            else {
                FlashMessages::addMessage(I18N::translate('The number of remaining occurences is not in a valid format'), 'danger');
            }
        }
        else {
            $task_schedule->setRemainingOccurences(0);
        }
        
        try {
            $this->taskschedules_service->update($task_schedule);
            return true;
        }
        catch(Exception $ex){
            Log::addErrorLog(sprintf('Error while updating the Task Schedule "%s". Exception: %s', $task_schedule->id(), $ex->getMessage()));
        }
        
        FlashMessages::addMessage(I18N::translate('An error occured while updating the scheduled task'), 'danger');
        Log::addConfigurationLog('Module '.$this->module->title().' : Task Schedule “'. $task_schedule->id() .'” could not be updated. See error log.');
        return false;
    }
    
    /**
     * Update general settings for the task, based on the request parameters
     *
     * @param TaskSchedule $task_schedule
     * @param ServerRequestInterface $request
     * @return bool
     */
    private function updateSpecificSettings(TaskSchedule $task_schedule, ServerRequestInterface $request) : bool
    {
        $task = $this->taskschedules_service->findTask($task_schedule->taskId());
        if($task === null || !($task instanceof ConfigurableTaskInterface)) return true;
        
        /** @var ConfigurableTaskInterface $task */
        if(!$task->updateConfig($request, $task_schedule)) {
            FlashMessages::addMessage(I18N::translate('An error occured while updating the specific settings of administrative task “%s”', $task->name()), 'danger');
            Log::addConfigurationLog('Module '.$this->module->getName().' : AdminTask “'. $task->getName() .'” specific settings could not be updated. See error log.');
        }
        
        return true;
    }

}

