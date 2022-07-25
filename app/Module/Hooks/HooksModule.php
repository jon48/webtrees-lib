<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Hooks
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2022, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\Hooks;

use Aura\Router\Map;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleConfigInterface;
use Fisharebest\Webtrees\Module\ModuleConfigTrait;
use Fisharebest\Webtrees\Services\MigrationService;
use MyArtJaub\Webtrees\Contracts\Hooks\HookServiceInterface;
use MyArtJaub\Webtrees\Contracts\Hooks\ModuleHookSubscriberInterface;
use MyArtJaub\Webtrees\Module\ModuleMyArtJaubInterface;
use MyArtJaub\Webtrees\Module\ModuleMyArtJaubTrait;
use MyArtJaub\Webtrees\Module\Hooks\Hooks\FactSourceTextExtenderCollector;
use MyArtJaub\Webtrees\Module\Hooks\Hooks\FamilyDatatablesExtenderCollector;
use MyArtJaub\Webtrees\Module\Hooks\Hooks\IndividualDatatablesExtenderCollector;
use MyArtJaub\Webtrees\Module\Hooks\Hooks\NameAccordionExtenderCollector;
use MyArtJaub\Webtrees\Module\Hooks\Hooks\RecordNameTextExtenderCollector;
use MyArtJaub\Webtrees\Module\Hooks\Hooks\SosaFamilyDatatablesExtenderCollector;
use MyArtJaub\Webtrees\Module\Hooks\Hooks\SosaIndividualDatatablesExtenderCollector;
use MyArtJaub\Webtrees\Module\Hooks\Hooks\SosaMissingDatatablesExtenderCollector;
use MyArtJaub\Webtrees\Module\Hooks\Http\RequestHandlers\AdminConfigPage;
use MyArtJaub\Webtrees\Module\Hooks\Http\RequestHandlers\ModulesHooksAction;
use MyArtJaub\Webtrees\Module\Hooks\Http\RequestHandlers\ModulesHooksPage;
use MyArtJaub\Webtrees\Module\Hooks\Services\HookService;

/**
 * MyArtJaub Hooks Module
 * Provide entry points to extend core webtrees code.
 */
class HooksModule extends AbstractModule implements
    ModuleMyArtJaubInterface,
    ModuleConfigInterface,
    ModuleHookSubscriberInterface
{
    use ModuleMyArtJaubTrait {
        boot as traitBoot;
    }
    use ModuleConfigTrait;

    // How to update the database schema for this module
    private const SCHEMA_TARGET_VERSION   = 2;
    private const SCHEMA_SETTING_NAME     = 'MAJ_HOOKS_SCHEMA_VERSION';
    private const SCHEMA_MIGRATION_PREFIX = __NAMESPACE__ . '\Schema';

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::title()
     */
    public function title(): string
    {
        return /* I18N: Name of the “Hooks” module */ I18N::translate('Hooks');
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::description()
     */
    public function description(): string
    {
        return /* I18N: Description of the “Hooks” module */ I18N::translate('Implements hooks management.');
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::boot()
     */
    public function boot(): void
    {
        $this->traitBoot();
        app()->bind(HookServiceInterface::class, HookService::class);
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

            $router->attach('', '/module-maj/hooks', static function (Map $router): void {

                $router->attach('', '/config/admin', static function (Map $router): void {

                    $router->get(AdminConfigPage::class, '', AdminConfigPage::class);
                    $router->get(ModulesHooksPage::class, '/{hook_name}', ModulesHooksPage::class);
                    $router->post(ModulesHooksAction::class, '/{hook_name}', ModulesHooksAction::class);
                });
            });
        });
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleCustomInterface::customModuleVersion()
     */
    public function customModuleVersion(): string
    {
        return '2.1.6-v.1';
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
     * @see \MyArtJaub\Webtrees\Contracts\Hooks\ModuleHookSubscriberInterface::listSubscribedHooks()
     */
    public function listSubscribedHooks(): array
    {
        return [
            app()->makeWith(FactSourceTextExtenderCollector::class, ['module' => $this]),
            app()->makeWith(FamilyDatatablesExtenderCollector::class, ['module' => $this]),
            app()->makeWith(IndividualDatatablesExtenderCollector::class, ['module' => $this]),
            app()->makeWith(NameAccordionExtenderCollector::class, ['module' => $this]),
            app()->makeWith(RecordNameTextExtenderCollector::class, ['module' => $this]),
            app()->makeWith(SosaFamilyDatatablesExtenderCollector::class, ['module' => $this]),
            app()->makeWith(SosaIndividualDatatablesExtenderCollector::class, ['module' => $this]),
            app()->makeWith(SosaMissingDatatablesExtenderCollector::class, ['module' => $this])
        ];
    }
}
