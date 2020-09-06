<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage PatronymicLineage
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2020, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\PatronymicLineage;

use Aura\Router\Map;
use Fisharebest\Webtrees\Factory;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Module\ModuleGlobalInterface;
use Fisharebest\Webtrees\Module\ModuleGlobalTrait;
use Fisharebest\Webtrees\Module\ModuleListInterface;
use Fisharebest\Webtrees\Module\ModuleListTrait;
use MyArtJaub\Webtrees\Module\AbstractModuleMaj;
use MyArtJaub\Webtrees\Module\PatronymicLineage\Http\RequestHandlers\LineagesPage;
use MyArtJaub\Webtrees\Module\PatronymicLineage\Http\RequestHandlers\SurnamesList;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Patronymic Lineage Module.
 * Display lineages of people with the same surname.
 */
class PatronymicLineageModule extends AbstractModuleMaj implements ModuleListInterface, ModuleGlobalInterface
{
    use ModuleListTrait;
    use ModuleGlobalTrait;

     /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::title()
     */
    public function title(): string
    {
        return /* I18N: Name of the “Patronymic lineage” module */ I18N::translate('Patronymic Lineages');
    }
    
    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\AbstractModule::description()
     */
    public function description(): string
    {
        //phpcs:ignore Generic.Files.LineLength.TooLong
        return /* I18N: Description of the “Patronymic lineage” module */ I18N::translate('Display lineages of people holding the same surname.');
    }
    
    /**
     * {@inheritDoc}
     * @see \MyArtJaub\Webtrees\Module\AbstractModuleMaj::loadRoutes()
     */
    public function loadRoutes(Map $router): void
    {
        $router->attach('', '', static function (Map $router) {

            $router->attach('', '/module-maj/lineages', static function (Map $router) {

                $router->attach('', '/Page', static function (Map $router) {

                    $router->get(SurnamesList::class, '/{tree}/list{/alpha}', SurnamesList::class);
                    $router->get(LineagesPage::class, '/{tree}/lineage/{surname}', LineagesPage::class);
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
     * @see \Fisharebest\Webtrees\Module\ModuleListInterface::listUrl()
     */
    public function listUrl(Tree $tree, array $parameters = []): string
    {
        $surname = $parameters['surname'] ?? '';
        
        $xref = app(ServerRequestInterface::class)->getAttribute('xref', '');
        if ($xref !== '' && $individual = Factory::individual()->make($xref, $tree)) {
            $surname = $individual->getAllNames()[$individual->getPrimaryName()]['surname'];
        }
        
        if ($surname !== '') {
            return route(LineagesPage::class, [
                'tree'      =>  $tree->name(),
                'surname'   =>  $surname
            ] + $parameters);
        }
        return route(SurnamesList::class, [
            'tree'  =>  $tree->name()
        ] + $parameters);
    }
    
    /**
     * {@inheritDoc}
     * @see \Fisharebest\Webtrees\Module\ModuleListInterface::listMenuClass()
     */
    public function listMenuClass(): string
    {
        return 'menu-maj-patrolineage';
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
