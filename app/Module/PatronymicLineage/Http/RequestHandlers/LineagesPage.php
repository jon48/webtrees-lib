<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage PatronymicLineage
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2020-2023, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\PatronymicLineage\Http\RequestHandlers;

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\Http\Exceptions\HttpNotFoundException;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Webtrees\Module\PatronymicLineage\PatronymicLineageModule;
use MyArtJaub\Webtrees\Module\PatronymicLineage\Model\LineageBuilder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Request handler for displaying lineages associated to a surname
 */
class LineagesPage implements RequestHandlerInterface
{
    use ViewResponseTrait;

    /**
     * @var PatronymicLineageModule|null $module
     */
    private $module;

    /**
     * Constructor for LineagesPage Request handler
     *
     * @param ModuleService $module_service
     */
    public function __construct(ModuleService $module_service)
    {
        $this->module = $module_service->findByInterface(PatronymicLineageModule::class)->first();
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

        $tree = Validator::attributes($request)->tree();
        $surname_attr = Validator::attributes($request)->string('surname', '');
        $surname = I18N::strtoupper(I18N::language()->normalize($surname_attr));

        if ($surname_attr !== $surname) {
            return Registry::responseFactory()
                ->redirect(LineagesPage::class, ['tree' => $tree->name(), 'surname' => $surname]);
        }

        if ($surname === '' ||  $surname === Individual::NOMEN_NESCIO) {
            return Registry::responseFactory()->redirect(SurnamesList::class, ['tree' => $tree->name()]);
        }

        $surn_initial = I18N::language()->initialLetter($surname);

        $all_surnames = $this->module->allSurnames($tree, false, false);
        $initials_list = collect($this->module->surnameInitials($all_surnames))
            ->reject(function (int $count, string $initial): bool {
                return $initial === '@' || $initial === ',';
            });
        $surname_variants = array_keys($all_surnames[$surname] ?? [$surname => $surname]);
        uasort($surname_variants, I18N::comparator());
        $surname_legend = implode('/', $surname_variants);

        $title = I18N::translate('Patronymic Lineages') . ' — ' . $surname_legend;

        $lineages = app()->make(LineageBuilder::class, ['surname' => $surname])->buildLineages();

        return $this->viewResponse($this->module->name() . '::lineages-page', [
            'title'         =>  $title,
            'module'        =>  $this->module,
            'tree'          =>  $tree,
            'initials_list' =>  $initials_list,
            'initial'       =>  $surn_initial,
            'show_all'      =>  'no',
            'surname'       =>  $surname,
            'surname_legend' =>  $surname_legend,
            'lineages'      =>  $lineages,
            'nb_lineages'   =>  $lineages !== null ? $lineages->count() : 0
        ]);
    }
}
