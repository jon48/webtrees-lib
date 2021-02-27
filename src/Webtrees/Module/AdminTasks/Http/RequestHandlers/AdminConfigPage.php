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
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\Services\ModuleService;
use Fisharebest\Webtrees\Services\UserService;
use MyArtJaub\Webtrees\Functions\Functions;
use MyArtJaub\Webtrees\Module\AdminTasks\AdminTasksModule;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Request handler for AdminConfigPage
 */
class AdminConfigPage implements RequestHandlerInterface
{
    use ViewResponseTrait;

    /**
     * @var AdminTasksModule $module
     */
    private $module;

    /**
     *
     * @var UserService $user_service
     */
    private $user_service;

    /**
     * Constructor for Admin Config request handler
     *
     * @param ModuleService $module_service
     * @param UserService $user_service
     */
    public function __construct(ModuleService $module_service, UserService $user_service)
    {
        $this->module = $module_service->findByInterface(AdminTasksModule::class)->first();
        $this->user_service = $user_service;
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

        $token = $this->module->getPreference('MAJ_AT_FORCE_EXEC_TOKEN');
        if ($token === '') {
            $token = Functions::generateRandomToken();
            $this->module->setPreference('PAT_FORCE_EXEC_TOKEN', $token);
        }

        return $this->viewResponse($this->module->name() . '::admin/config', [
            'title'             =>  $this->module->title(),
            'trigger_token'     =>  $token,
            'trigger_route'     =>  route(TaskTrigger::class, ['task' => '__TASKNAME__', 'force' => '__TOKEN__']),
            'new_token_route'   =>  route(TokenGenerate::class),
            'tasks_data_route'  =>  route(TasksList::class)
        ]);
    }
}