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

namespace MyArtJaub\Webtrees\Module\AdminTasks;

use Aura\Router\Map;
use Fig\Http\Message\RequestMethodInterface;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Http\Middleware\AuthAdministrator;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleConfigInterface;
use Fisharebest\Webtrees\Module\ModuleConfigTrait;
use Fisharebest\Webtrees\Module\ModuleGlobalInterface;
use Fisharebest\Webtrees\Module\ModuleGlobalTrait;
use Fisharebest\Webtrees\Services\MigrationService;
use MyArtJaub\Webtrees\Module\ModuleMyArtJaubInterface;
use MyArtJaub\Webtrees\Module\ModuleMyArtJaubTrait;
use MyArtJaub\Webtrees\Module\AdminTasks\Contracts\ModuleTasksProviderInterface;
use MyArtJaub\Webtrees\Module\AdminTasks\Http\RequestHandlers\AdminConfigPage;
use MyArtJaub\Webtrees\Module\AdminTasks\Http\RequestHandlers\TaskEditAction;
use MyArtJaub\Webtrees\Module\AdminTasks\Http\RequestHandlers\TaskEditPage;
use MyArtJaub\Webtrees\Module\AdminTasks\Http\RequestHandlers\TaskStatusAction;
use MyArtJaub\Webtrees\Module\AdminTasks\Http\RequestHandlers\TaskTrigger;
use MyArtJaub\Webtrees\Module\AdminTasks\Http\RequestHandlers\TasksList;
use MyArtJaub\Webtrees\Module\AdminTasks\Http\RequestHandlers\TokenGenerate;
use MyArtJaub\Webtrees\Module\AdminTasks\Tasks\HealthCheckEmailTask;

/**
 * MyArtJaub AdminTask Module
 * Allow for tasks to be run on a (nearly-)regular schedule
 */
class AdminTasksModule extends AbstractModule implements
    ModuleMyArtJaubInterface,
    ModuleConfigInterface,
    ModuleGlobalInterface,
    ModuleTasksProviderInterface
{
    use ModuleMyArtJaubTrait {
        boot as traitBoot;
    }
    use ModuleConfigTrait;
    use ModuleGlobalTrait;

    //How to update the database schema for this module
    private const SCHEMA_TARGET_VERSION   = 2;
    private const SCHEMA_SETTING_NAME     = 'MAJ_ADMTASKS_SCHEMA_VERSION';
    private const SCHEMA_MIGRATION_PREFIX = __NAMESPACE__ . '\Schema';

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::title()
     */
    public function title(): string
    {
        return I18N::translate('Administration Tasks');
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::description()
     */
    public function description(): string
    {
        return I18N::translate('Manage and run nearly-scheduled administration tasks.');
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::boot()
     */
    public function boot(): void
    {
        $this->traitBoot();
        app(MigrationService::class)->updateSchema(
            self::SCHEMA_MIGRATION_PREFIX,
            self::SCHEMA_SETTING_NAME,
            self::SCHEMA_TARGET_VERSION
        );
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Module\ModuleMyArtJaubInterface::loadRoutes()
     */
    public function loadRoutes(Map $router): void
    {
        $router->attach('', '', static function (Map $router): void {

            $router->attach('', '/module-maj/admintasks', static function (Map $router): void {

                $router->attach('', '/admin', static function (Map $router): void {

                    $router->extras([
                        'middleware' => [
                            AuthAdministrator::class,
                        ],
                    ]);
                    $router->get(AdminConfigPage::class, '/config', AdminConfigPage::class);

                    $router->attach('', '/tasks', static function (Map $router): void {

                        $router->get(TasksList::class, '', TasksList::class);
                        $router->get(TaskEditPage::class, '/{task}', TaskEditPage::class);
                        $router->post(TaskEditAction::class, '/{task}', TaskEditAction::class);
                        $router->get(TaskStatusAction::class, '/{task}/status/{enable}', TaskStatusAction::class);
                    });
                });

                $router->get(TaskTrigger::class, '/trigger{/task}', TaskTrigger::class)
                    ->allows(RequestMethodInterface::METHOD_POST);

                $router->post(TokenGenerate::class, '/token', TokenGenerate::class)
                    ->extras(['middleware' => [AuthAdministrator::class]]);
            });
        });
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleCustomInterface::customModuleLatestVersion()
     */
    public function customModuleVersion(): string
    {
        return '2.0.11-v.2';
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleConfigInterface::getConfigLink()
     */
    public function getConfigLink(): string
    {
        return route(AdminConfigPage::class);
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleGlobalInterface::bodyContent()
     */
    public function bodyContent(): string
    {
        return view($this->name() . '::snippet', [ 'url' => route(TaskTrigger::class) ]);
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Module\AdminTasks\Contracts\ModuleTasksProviderInterface::listTasks()
     */
    public function listTasks(): array
    {
        return [
            'maj-healthcheck' => HealthCheckEmailTask::class
        ];
    }
}
