<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Sosa
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2020, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\Sosa;

use Aura\Router\Map;
use Aura\Router\Route;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Menu;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Http\RequestHandlers\IndividualPage;
use Fisharebest\Webtrees\Module\ModuleGlobalInterface;
use Fisharebest\Webtrees\Module\ModuleGlobalTrait;
use Fisharebest\Webtrees\Module\ModuleMenuInterface;
use Fisharebest\Webtrees\Module\ModuleMenuTrait;
use Fisharebest\Webtrees\Services\MigrationService;
use MyArtJaub\Webtrees\Module\AbstractModuleMaj;
use MyArtJaub\Webtrees\Module\Sosa\Http\RequestHandlers\AncestorsList;
use MyArtJaub\Webtrees\Module\Sosa\Http\RequestHandlers\AncestorsListFamily;
use MyArtJaub\Webtrees\Module\Sosa\Http\RequestHandlers\AncestorsListIndividual;
use MyArtJaub\Webtrees\Module\Sosa\Http\RequestHandlers\MissingAncestorsList;
use MyArtJaub\Webtrees\Module\Sosa\Http\RequestHandlers\SosaComputeAction;
use MyArtJaub\Webtrees\Module\Sosa\Http\RequestHandlers\SosaComputeModal;
use MyArtJaub\Webtrees\Module\Sosa\Http\RequestHandlers\SosaConfig;
use MyArtJaub\Webtrees\Module\Sosa\Http\RequestHandlers\SosaConfigAction;
use MyArtJaub\Webtrees\Module\Sosa\Http\RequestHandlers\SosaStatistics;
use Psr\Http\Message\ServerRequestInterface;

/**
 * MyArtJaub Sosa Module
 * Identify and produce statistics about Sosa ancestors
 */
class SosaModule extends AbstractModuleMaj implements ModuleGlobalInterface, ModuleMenuInterface
{
    use ModuleGlobalTrait;
    use ModuleMenuTrait;

// How to update the database schema for this module


    private const SCHEMA_TARGET_VERSION   = 3;
    private const SCHEMA_SETTING_NAME     = 'MAJ_SOSA_SCHEMA_VERSION';
    private const SCHEMA_MIGRATION_PREFIX = __NAMESPACE__ . '\Schema';
/**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::title()
     */
    public function title(): string
    {
        return /* I18N: Name of the “Sosa” module */ I18N::translate('Sosa');
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::description()
     */
    public function description(): string
    {
        //phpcs:ignore Generic.Files.LineLength.TooLong
        return /* I18N: Description of the “Sosa” module */ I18N::translate('Calculate and display Sosa ancestors of the root person.');
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Module\AbstractModuleMaj::boot()
     */
    public function boot(): void
    {
        parent::boot();
        app(MigrationService::class)->updateSchema(
            self::SCHEMA_MIGRATION_PREFIX,
            self::SCHEMA_SETTING_NAME,
            self::SCHEMA_TARGET_VERSION
        );
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Module\AbstractModuleMaj::loadRoutes()
     */
    public function loadRoutes(Map $router): void
    {
        $router->attach('', '', static function (Map $router): void {

            $router->attach('', '/module-maj/sosa', static function (Map $router): void {

                $router->attach('', '/list', static function (Map $router): void {


                    $router->get(AncestorsList::class, '/ancestors/{tree}{/gen}', AncestorsList::class);
                    $router->get(AncestorsListIndividual::class, '/ancestors/{tree}/{gen}/tab/individuals', AncestorsListIndividual::class);    //phpcs:ignore Generic.Files.LineLength.TooLong
                    $router->get(AncestorsListFamily::class, '/ancestors/{tree}/{gen}/tab/families', AncestorsListFamily::class);   //phpcs:ignore Generic.Files.LineLength.TooLong
                    $router->get(MissingAncestorsList::class, '/missing/{tree}{/gen}', MissingAncestorsList::class);
                });
                $router->get(SosaStatistics::class, '/statistics/{tree}', SosaStatistics::class);
                $router->attach('', '/config/{tree}', static function (Map $router): void {


                    $router->get(SosaConfig::class, '', SosaConfig::class);
                    $router->post(SosaConfigAction::class, '', SosaConfigAction::class);
                    $router->get(SosaComputeModal::class, '/compute/{xref}', SosaComputeModal::class);
                    $router->post(SosaComputeAction::class, '/compute', SosaComputeAction::class);
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
        return '2.0.7-v.1';
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleMenuInterface::defaultMenuOrder()
     */
    public function defaultMenuOrder(): int
    {
        return 7;
    }

    /**
     * {@inhericDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleMenuInterface::getMenu()
     */
    public function getMenu(Tree $tree): ?Menu
    {
        $menu = new Menu(I18N::translate('Sosa Statistics'));
        $menu->setClass('menu-maj-sosa');
        $menu->setSubmenus([
            new Menu(
                I18N::translate('Sosa Ancestors'),
                route(AncestorsList::class, ['tree' => $tree->name()]),
                'menu-maj-sosa-list',
                ['rel' => 'nofollow']
            ),
            new Menu(
                I18N::translate('Missing Ancestors'),
                route(MissingAncestorsList::class, ['tree' => $tree->name()]),
                'menu-maj-sosa-missing',
                ['rel' => 'nofollow']
            ),
            new Menu(
                I18N::translate('Sosa Statistics'),
                route(SosaStatistics::class, ['tree' => $tree->name()]),
                'menu-maj-sosa-stats'
            )
        ]);

        if (Auth::check()) {
            $menu->addSubmenu(new Menu(
                I18N::translate('Sosa Configuration'),
                route(SosaConfig::class, ['tree' => $tree->name()]),
                'menu-maj-sosa-config'
            ));

            /** @var ServerRequestInterface $request */
            $request = app(ServerRequestInterface::class);
            $route = $request->getAttribute('route');
            assert($route instanceof Route);

            $root_indi_id = $tree->getUserPreference(Auth::user(), 'MAJ_SOSA_ROOT_ID');

            if ($route->name === IndividualPage::class && mb_strlen($root_indi_id) > 0) {
                $xref = $request->getAttribute('xref');
                assert(is_string($xref));

                $menu->addSubmenu(new Menu(
                    I18N::translate('Complete Sosas'),
                    '#',
                    'menu-maj-sosa-compute',
                    [
                        'rel'           => 'nofollow',
                        'data-href'     => route(SosaComputeModal::class, ['tree' => $tree->name(), 'xref' => $xref]),
                        'data-target'   => '#wt-ajax-modal',
                        'data-toggle'   => 'modal',
                        'data-backdrop' => 'static'
                    ]
                ));
            }
        }

        return $menu;
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleGlobalInterface::headContent()
     */
    public function headContent(): string
    {
        return '<link rel="stylesheet" href="' . e($this->moduleCssUrl()) . '">';
    }
}
