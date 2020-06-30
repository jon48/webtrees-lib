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

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Exceptions\HttpNotFoundException;
use Fisharebest\Webtrees\Services\DatatablesService;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Webtrees\Module\AdminTasks\AdminTasksModule;
use MyArtJaub\Webtrees\Module\AdminTasks\Model\TaskSchedule;
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
    /**
     * @var AdminTasksModule $module
     */
    private $module;
    
    /**
     * @var TaskScheduleService $taskschedules_service
     */
    private $taskschedules_service;
    
    /**
     * @var DatatablesService $datatables_service
     */
    private $datatables_service;
    
    /**
     * Constructor for TasksList Request Handler
     * 
     * @param ModuleService $module_service
     * @param TaskScheduleService $taskschedules_service
     * @param DatatablesService $datatables_service
     */
    function __construct(
        ModuleService $module_service,
        TaskScheduleService $taskschedules_service,
        DatatablesService $datatables_service
    ) {
            $this->module = $module_service->findByInterface(AdminTasksModule::class)->first();
            $this->taskschedules_service = $taskschedules_service;
            $this->datatables_service = $datatables_service;
    }
    
    /**
     * {@inheritDoc}
     * @see \Psr\Http\Server\RequestHandlerInterface::handle()
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        if($this->module === null)
            throw new HttpNotFoundException(I18N::translate('The attached module could not be found.'));
        
        $task_schedules = $this->taskschedules_service->all(true, true)
            ->map(function (TaskSchedule $value) {
                $row = $value->toArray();
                $task = $this->taskschedules_service->findTask($row['task_id']);
                $row['task_name'] = $task !== null ? $task->name() : I18N::translate('Task not found');
                return $row;
            });
            
        $search_columns = ['task_name'];
        $sort_columns   = ['task_name', 'enabled', 'last_run'];
        $module_name = $this->module->name();
        
        $callback = function (array $row) use ($module_name) : array {
            $row['frequency']->setLocale(I18N::locale()->code());
            
            $task_options_params = [
                'task_sched_id' => $row['id'],
                'task_sched_enabled' => $row['enabled'],
                'task_edit_route' => route(TaskEditPage::class, ['task' => $row['id']]),
                'task_status_route' => route(TaskStatusAction::class, [
                    'task' => $row['id'], 
                    'enable' => $row['enabled'] ? 0 : 1
                ])
            ];
            
            $task_run_params = [
                'task_sched_id' => $row['id'],
                'run_route' => route(TaskTrigger::class, [
                    'task'  =>  $row['task_id'],
                    'force' =>  $this->module->getPreference('MAJ_AT_FORCE_EXEC_TOKEN')
                ])
            ];
            
            $datum = [
                view($module_name . '::admin/tasks-table-options', $task_options_params),
                view($module_name . '::components/yes-no-icons', ['yes' => $row['enabled']]),
                '<span dir="auto">' . e($row['task_name']) . '</span>',
                $row['last_run']->unix() === 0 ?
                view('components/datetime', ['timestamp' => $row['last_run']]) :
                view('components/datetime-diff', ['timestamp' => $row['last_run']]),
                view($module_name . '::components/yes-no-icons', ['yes' => $row['last_result']]),
                '<span dir="auto">' . e($row['frequency']->cascade()->forHumans()) . '</span>',
                $row['nb_occurrences'] > 0 ? I18N::number($row['nb_occurrences']) : I18N::translate('Unlimited'),
                view($module_name . '::components/yes-no-icons', ['yes' => $row['is_running'], 'text_yes' => I18N::translate('Running'), 'text_no' => I18N::translate('Not running')]),
                view($module_name . '::admin/tasks-table-run', $task_run_params)
            ];
            
            return $datum;
        };
        
        return $this->datatables_service->handleCollection($request, $task_schedules, $search_columns, $sort_columns, $callback);
    }
}

