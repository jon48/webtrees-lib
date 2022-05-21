<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage AdminTasks
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2012-2022, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\AdminTasks\Http\RequestHandlers;

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\Http\Exceptions\HttpNotFoundException;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Webtrees\Module\AdminTasks\AdminTasksModule;
use MyArtJaub\Webtrees\Module\AdminTasks\Services\TaskScheduleService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Request handler for triggering task schedules
 */
class TaskTrigger implements RequestHandlerInterface
{
    private ?AdminTasksModule $module;
    private TaskScheduleService $taskschedules_service;

    /**
     * Constructor for TaskTrigger request handler
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
        if ($this->module === null) {
            throw new HttpNotFoundException(I18N::translate('The attached module could not be found.'));
        }

        $task_id = Validator::attributes($request)->string('task', '');
        $token = $this->module->getPreference('MAJ_AT_FORCE_EXEC_TOKEN');
        $force_token = Validator::queryParams($request)->string('force', '');
        $force = $token !== '' &&  $token === $force_token;

        $task_schedules = $this->taskschedules_service->findTasksToRun($force, $task_id);

        foreach ($task_schedules as $task_schedule) {
            $this->taskschedules_service->run($task_schedule, $force);
        }

        return Registry::responseFactory()->response();
    }
}
