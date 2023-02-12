<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage AdminTasks
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2012-2023, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\AdminTasks\Http\RequestHandlers;

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Http\Exceptions\HttpNotFoundException;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Webtrees\Module\AdminTasks\AdminTasksModule;
use MyArtJaub\Webtrees\Module\AdminTasks\Services\TokenService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Request handler for AdminConfigPage
 */
class AdminConfigPage implements RequestHandlerInterface
{
    use ViewResponseTrait;

    private ?AdminTasksModule $module;
    private TokenService $token_service;

    /**
     * Constructor for Admin Config request handler
     *
     * @param ModuleService $module_service
     */
    public function __construct(ModuleService $module_service, TokenService $token_service)
    {
        $this->module = $module_service->findByInterface(AdminTasksModule::class)->first();
        $this->token_service = $token_service;
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
            $token = $this->token_service->generateRandomToken();
            $this->module->setPreference('MAJ_AT_FORCE_EXEC_TOKEN', $token);
        }

        return $this->viewResponse($this->module->name() . '::admin/config', [
            'title'             =>  $this->module->title(),
            'trigger_token'     =>  $token,
            'trigger_route'     =>  route(TaskTrigger::class, ['task' => '__TASKNAME__', 'force' => '__TOKEN__']),
            'new_token_route'   =>  route(TokenGenerate::class),
            'tasks_data_route'  =>  route(TasksList::class),
            'js_script_url'     =>  $this->module->assetUrl('js/admintasks.min.js')
        ]);
    }
}
