<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage PatronymicLineage
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2020, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\PatronymicLineage\Http\RequestHandlers;

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Exceptions\HttpNotFoundException;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\Module\IndividualListModule;
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
     * @var PatronymicLineageModule $module
     */
    private $module;

    /**
     * @var IndividualListModule $indilist_module
     */
    private $indilist_module;

    /**
     * Constructor for LineagesPage Request handler
     *
     * @param ModuleService $module_service
     */
    public function __construct(ModuleService $module_service)
    {
        $this->module = $module_service->findByInterface(PatronymicLineageModule::class)->first();
        $this->indilist_module = $module_service->findByInterface(IndividualListModule::class)->first();
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

        if ($this->indilist_module === null) {
            throw new HttpNotFoundException(I18N::translate('There is no module to handle individual lists.'));
        }

        $tree = $request->getAttribute('tree');
        assert($tree instanceof Tree);

        $surname = $request->getAttribute('surname');

        $initial = mb_substr($surname, 0, 1);
        $initials_list = collect($this->indilist_module->surnameAlpha($tree, false, false, I18N::locale()))
            ->reject(function ($count, $initial): bool {

                return $initial === '@' || $initial === ',';
            });

        $title = I18N::translate('Patronymic Lineages') . ' — ' . $surname;

        $lineages = app()->make(LineageBuilder::class, ['surname' => $surname])->buildLineages();

        return $this->viewResponse($this->module->name() . '::lineages-page', [
            'title'         =>  $title,
            'module'        =>  $this->module,
            'tree'          =>  $tree,
            'initials_list' =>  $initials_list,
            'initial'       =>  $initial,
            'show_all'      =>  'no',
            'surname'       =>  $surname,
            'lineages'      =>  $lineages,
            'nb_lineages'   =>  $lineages !== null ? $lineages->count() : 0
        ]);
    }
}
