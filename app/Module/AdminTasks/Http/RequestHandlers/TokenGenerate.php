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
use Fisharebest\Webtrees\Log;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Http\Exceptions\HttpNotFoundException;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Webtrees\Module\AdminTasks\AdminTasksModule;
use MyArtJaub\Webtrees\Module\AdminTasks\Services\TokenService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Request handler for generating a new token
 */
class TokenGenerate implements RequestHandlerInterface
{
    private ?AdminTasksModule $module;
    private TokenService $token_service;

    /**
     * Constructor for TokenGenerate request handler
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
        if ($this->module === null) {
            throw new HttpNotFoundException(I18N::translate('The attached module could not be found.'));
        }

        $token = $this->token_service->generateRandomToken();
        $this->module->setPreference('MAJ_AT_FORCE_EXEC_TOKEN', $token);
        Log::addConfigurationLog($this->module->title() . ' : New token generated.');

        return Registry::responseFactory()->response(['token' => $token]);
    }
}
