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
use Fisharebest\Webtrees\DefaultUser;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Menu;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Http\RequestHandlers\IndividualPage;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Module\ModuleGlobalInterface;
use Fisharebest\Webtrees\Module\ModuleGlobalTrait;
use Fisharebest\Webtrees\Module\ModuleMenuInterface;
use Fisharebest\Webtrees\Module\ModuleMenuTrait;
use Fisharebest\Webtrees\Module\ModuleSidebarInterface;
use Fisharebest\Webtrees\Module\ModuleSidebarTrait;
use Fisharebest\Webtrees\Services\MigrationService;
use MyArtJaub\Webtrees\Contracts\GeoDispersion\ModuleGeoAnalysisProviderInterface;
use MyArtJaub\Webtrees\Contracts\Hooks\ModuleHookSubscriberInterface;
use MyArtJaub\Webtrees\Module\ModuleMyArtJaubInterface;
use MyArtJaub\Webtrees\Module\ModuleMyArtJaubTrait;
use MyArtJaub\Webtrees\Module\Sosa\GeoAnalyses\SosaByGenerationGeoAnalysis;
use MyArtJaub\Webtrees\Module\Sosa\Hooks\SosaIconHook;
use MyArtJaub\Webtrees\Module\Sosa\Http\RequestHandlers\AncestorsList;
use MyArtJaub\Webtrees\Module\Sosa\Http\RequestHandlers\AncestorsListFamily;
use MyArtJaub\Webtrees\Module\Sosa\Http\RequestHandlers\AncestorsListIndividual;
use MyArtJaub\Webtrees\Module\Sosa\Http\RequestHandlers\MissingAncestorsList;
use MyArtJaub\Webtrees\Module\Sosa\Http\RequestHandlers\PedigreeCollapseData;
use MyArtJaub\Webtrees\Module\Sosa\Http\RequestHandlers\SosaComputeAction;
use MyArtJaub\Webtrees\Module\Sosa\Http\RequestHandlers\SosaComputeModal;
use MyArtJaub\Webtrees\Module\Sosa\Http\RequestHandlers\SosaConfig;
use MyArtJaub\Webtrees\Module\Sosa\Http\RequestHandlers\SosaConfigAction;
use MyArtJaub\Webtrees\Module\Sosa\Http\RequestHandlers\SosaStatistics;
use MyArtJaub\Webtrees\Module\Sosa\Services\SosaRecordsService;
use Psr\Http\Message\ServerRequestInterface;

/**
 * MyArtJaub Sosa Module
 * Identify and produce statistics about Sosa ancestors
 */
class SosaModule extends AbstractModule implements
    ModuleMyArtJaubInterface,
    ModuleGlobalInterface,
    ModuleMenuInterface,
    ModuleSidebarInterface,
    ModuleGeoAnalysisProviderInterface,
    ModuleHookSubscriberInterface
{
    use ModuleMyArtJaubTrait {
        boot as traitBoot;
    }
    use ModuleGlobalTrait;
    use ModuleMenuTrait;
    use ModuleSidebarTrait;

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

            $router->attach('', '/module-maj/sosa', static function (Map $router): void {

                $router->attach('', '/list', static function (Map $router): void {
                    $router->tokens(['gen' => '\d+']);
                    $router->get(AncestorsList::class, '/ancestors/{tree}{/gen}', AncestorsList::class);
                    $router->get(AncestorsListIndividual::class, '/ancestors/{tree}/{gen}/tab/individuals', AncestorsListIndividual::class);    //phpcs:ignore Generic.Files.LineLength.TooLong
                    $router->get(AncestorsListFamily::class, '/ancestors/{tree}/{gen}/tab/families', AncestorsListFamily::class);   //phpcs:ignore Generic.Files.LineLength.TooLong
                    $router->get(MissingAncestorsList::class, '/missing/{tree}{/gen}', MissingAncestorsList::class);
                });

                $router->attach('', '/statistics/{tree}', static function (Map $router): void {

                    $router->get(SosaStatistics::class, '', SosaStatistics::class);
                    $router->get(PedigreeCollapseData::class, '/pedigreecollapse', PedigreeCollapseData::class);
                });

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
        return '2.1.0-v.1';
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

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleGlobalInterface::bodyContent()
     */
    public function bodyContent(): string
    {
        return '<script src="' . $this->assetUrl('js/sosa.min.js') . '"></script>';
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleSidebarInterface::sidebarTitle()
     */
    public function sidebarTitle(): string
    {
        $request = app(ServerRequestInterface::class);
        $xref = $request->getAttribute('xref');
        $tree = $request->getAttribute('tree');
        $user = Auth::check() ? Auth::user() : new DefaultUser();

        $individual = Registry::individualFactory()->make($xref, $tree);

        return view($this->name() . '::sidebar/title', [
            'module_name'   =>  $this->name(),
            'sosa_numbers'  =>  app(SosaRecordsService::class)->sosaNumbers($tree, $user, $individual)
        ]);
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleSidebarInterface::getSidebarContent()
     */
    public function getSidebarContent(Individual $individual): string
    {
        $sosa_root_xref = $individual->tree()->getUserPreference(Auth::user(), 'MAJ_SOSA_ROOT_ID');
        $sosa_root = Registry::individualFactory()->make($sosa_root_xref, $individual->tree());
        $user = Auth::check() ? Auth::user() : new DefaultUser();

        return view($this->name() . '::sidebar/content', [
            'sosa_ancestor' =>  $individual,
            'sosa_root'     =>  $sosa_root,
            'sosa_numbers'  =>  app(SosaRecordsService::class)->sosaNumbers($individual->tree(), $user, $individual)
        ]);
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleSidebarInterface::hasSidebarContent()
     */
    public function hasSidebarContent(Individual $individual): bool
    {
        $user = Auth::check() ? Auth::user() : new DefaultUser();

        return app(SosaRecordsService::class)
            ->sosaNumbers($individual->tree(), $user, $individual)->count() > 0;
    }

    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleSidebarInterface::defaultSidebarOrder()
     */
    public function defaultSidebarOrder(): int
    {
        return 1;
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\GeoDispersion\ModuleGeoAnalysisProviderInterface::listGeoAnalyses()
     */
    public function listGeoAnalyses(): array
    {
        return [
            SosaByGenerationGeoAnalysis::class
        ];
    }

    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Contracts\Hooks\ModuleHookSubscriberInterface::listSubscribedHooks()
     */
    public function listSubscribedHooks(): array
    {
        return [
            app()->makeWith(SosaIconHook::class, [ 'module' => $this ])
        ];
    }
}
