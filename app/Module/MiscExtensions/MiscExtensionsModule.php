<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage MiscExtensions
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2021, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\MiscExtensions;

use Aura\Router\Map;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\View;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleConfigInterface;
use Fisharebest\Webtrees\Module\ModuleConfigTrait;
use MyArtJaub\Webtrees\Contracts\Hooks\ModuleHookSubscriberInterface;
use MyArtJaub\Webtrees\Module\ModuleMyArtJaubInterface;
use MyArtJaub\Webtrees\Module\ModuleMyArtJaubTrait;
use MyArtJaub\Webtrees\Module\MiscExtensions\Hooks\TitlesCardHook;
use MyArtJaub\Webtrees\Module\MiscExtensions\Http\RequestHandlers\AdminConfigAction;
use MyArtJaub\Webtrees\Module\MiscExtensions\Http\RequestHandlers\AdminConfigPage;

/**
 * MyArtJaub Miscellaneous Extensions Module
 * Provide miscellaneous improvements to webtrees.
 */
class MiscExtensionsModule extends AbstractModule implements
    ModuleMyArtJaubInterface,
    ModuleConfigInterface,
    ModuleHookSubscriberInterface
{
    use ModuleMyArtJaubTrait {
        boot as traitBoot;
    }
    use ModuleConfigTrait;

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::title()
     */
    public function title(): string
    {
        return /* I18N: Name of the “MiscExtensions” module */ I18N::translate('Miscellaneous extensions');
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::description()
     */
    public function description(): string
    {
        //phpcs:ignore Generic.Files.LineLength.TooLong
        return /* I18N: Description of the “MiscExtensions” module */ I18N::translate('Miscellaneous extensions for webtrees.');
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::boot()
     */
    public function boot(): void
    {
        $this->traitBoot();
        View::registerCustomView('::modules/privacy-policy/page', $this->name() . '::privacy-policy');
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Module\ModuleMyArtJaubInterface::loadRoutes()
     */
    public function loadRoutes(Map $router): void
    {
        $router->attach('', '', static function (Map $router): void {

            $router->attach('', '/module-maj/misc', static function (Map $router): void {

                $router->attach('', '/config/admin', static function (Map $router): void {

                    $router->get(AdminConfigPage::class, '', AdminConfigPage::class);
                    $router->post(AdminConfigAction::class, '', AdminConfigAction::class);
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
        return '2.1.1-v.1';
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
            app()->makeWith(TitlesCardHook::class, [ 'module' => $this ])
        ];
    }
}
