<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage PatronymicLineage
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2026, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\PatronymicLineage;

use Aura\Router\Map;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\Module\IndividualListModule;
use Fisharebest\Webtrees\Module\ModuleGlobalInterface;
use Fisharebest\Webtrees\Module\ModuleGlobalTrait;
use Fisharebest\Webtrees\Module\ModuleListInterface;
use Fisharebest\Webtrees\Module\ModuleListTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use MyArtJaub\Webtrees\Module\ModuleMyArtJaubInterface;
use MyArtJaub\Webtrees\Module\ModuleMyArtJaubTrait;
use MyArtJaub\Webtrees\Module\PatronymicLineage\Http\RequestHandlers\LineagesPage;
use MyArtJaub\Webtrees\Module\PatronymicLineage\Http\RequestHandlers\SurnamesList;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionMethod;

/**
 * Patronymic Lineage Module.
 * Display lineages of people with the same surname.
 */
class PatronymicLineageModule extends IndividualListModule implements
    ModuleMyArtJaubInterface,
    ModuleListInterface,
    ModuleGlobalInterface
{
    use ModuleMyArtJaubTrait;
    use ModuleListTrait;
    use ModuleGlobalTrait;

    #[\Override]
    public function title(): string
    {
        return /* I18N: Name of the “Patronymic lineage” module */ I18N::translate('Patronymic Lineages');
    }

    #[\Override]
    public function description(): string
    {
        //phpcs:ignore Generic.Files.LineLength.TooLong
        return /* I18N: Description of the “Patronymic lineage” module */ I18N::translate('Display lineages of people holding the same surname.');
    }

    #[\Override]
    public function loadRoutes(Map $router): void
    {
        $router->attach('', '', static function (Map $router): void {

            $router->attach('', '/module-maj/lineages', static function (Map $router): void {

                $router->attach('', '/Page', static function (Map $router): void {

                    $router->get(SurnamesList::class, '/{tree}/list{/alpha}', SurnamesList::class);
                    $router->get(LineagesPage::class, '/{tree}/lineage/{surname}', LineagesPage::class);
                });
            });
        });
    }

    #[\Override]
    public function customModuleVersion(): string
    {
        return '2.1.18-v.1';
    }

    #[\Override]
    public function listUrl(Tree $tree, array $parameters = []): string
    {
        $surname = $parameters['surname'] ?? null;
        $surname = is_string($surname) ? $surname : null;

        $request = app(ServerRequestInterface::class);

        // If a surname is already in the query attribute, use it
        if ($surname === null) {
            $surname_attr =  Validator::attributes($request)->string('surname', '');
            $surname_param =  Validator::queryParams($request)->string('surname', '');
            $surname_body =  Validator::parsedBody($request)->string('surname', '');
            $surname = $surname_attr !== '' ? $surname_attr : (
                $surname_param !== '' ? $surname_param : (
                $surname_body !== '' ? $surname_body : null
            ));
        }

        // If nothing found, and on an individual page, use its name
        $xref = Validator::attributes($request)->isXref()->string('xref', '');
        if ($xref !== '') {
            $individual = Registry::individualFactory()->make($xref, $tree);
            if ($individual instanceof Individual && $individual->canShow()) {
                $primary_name = $individual->getPrimaryName();
                $surname ??= $individual->getAllNames()[$primary_name]['surn'] ?? null;
            }
        }

        if (Str::length($surname ?? '') > 0 && $surname !== Individual::NOMEN_NESCIO) {
            return route(LineagesPage::class, [
                'tree'      =>  $tree->name(),
                'surname'   =>  $surname
            ] + $parameters);
        }
        return route(SurnamesList::class, ['tree'  =>  $tree->name() ] + $parameters);
    }

    #[\Override]
    public function listMenuClass(): string
    {
        return 'menu-maj-patrolineage';
    }

    #[\Override]
    public function headContent(): string
    {
        return '<link rel="stylesheet" href="' . e($this->moduleCssUrl()) . '">';
    }

    /**
     * Implemented to set the visibility to public.
     * This should probably be in a service, but this hack allows for reuse of mainstream code.
     */
    #[\Override]
    public function individuals(
        Tree $tree,
        array $surns_to_show,
        string $galpha,
        bool $marnm,
        bool $fams
    ): Collection {
        return parent::individuals($tree, $surns_to_show, $galpha, $marnm, $fams);
    }

    /**
     * Implemented to set the visibility to public.
     * This should probably be in a service, but this hack allows for reuse of mainstream code.
     *
     * @return array<object{n_surn:string,n_surname:string,total:int}>
     */
    public function surnameData(Tree $tree, bool $marnm, bool $fams): array
    {
        $reflectionMethod = new ReflectionMethod(IndividualListModule::class, 'surnameData');
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod->invoke($this, $tree, $marnm, $fams);
    }

    /**
     * Implemented to set the visibility to public.
     * This should probably be in a service, but this hack allows for reuse of mainstream code.
     */
    #[\Override]
    public function allSurnames(array $surname_data): array
    {
        return parent::allSurnames($surname_data);
    }

    /**
     * Implemented to set the visibility to public.
     * This should probably be in a service, but this hack allows for reuse of mainstream code.
     */
    #[\Override]
    public function surnameInitials(array $surname_data): array
    {
        return parent::surnameInitials($surname_data);
    }
}
