<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage PatronymicLineage
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2020-2022, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\PatronymicLineage\Http\RequestHandlers;

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\Http\Exceptions\HttpNotFoundException;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Webtrees\Module\PatronymicLineage\PatronymicLineageModule;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Request handler for displaying list of surnames
 */
class SurnamesList implements RequestHandlerInterface
{
    use ViewResponseTrait;

    private ?PatronymicLineageModule $module;

    /**
     * Constructor for SurnamesList Request Handler
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
        $initial = Validator::attributes($request)->string('alpha', '');

        $all_surnames = $this->module->allSurnames($tree, false, false);
        $initials_list = array_filter(
            $this->module->surnameInitials($all_surnames),
            static fn (string $x): bool => $x !== '@' && $x !== ',',
            ARRAY_FILTER_USE_KEY
        );

        $show_all = Validator::queryParams($request)->string('show_all', 'no') === 'yes';

        if ($show_all) {
            $title = I18N::translate('Patronymic Lineages') . ' — ' . I18N::translate('All');
            $surnames = array_filter(
                $all_surnames,
                static fn (string $x): bool => $x !== '' && $x !== Individual::NOMEN_NESCIO,
                ARRAY_FILTER_USE_KEY
            );
        } elseif (array_key_exists($initial, $initials_list)) {
            $title = I18N::translate('Patronymic Lineages') . ' — ' . $initial;
            $surnames = array_filter(
                $all_surnames,
                static fn (string $x): bool => I18N::language()->initialLetter($x) === $initial,
                ARRAY_FILTER_USE_KEY
            );
        } else {
            $title =  I18N::translate('Patronymic Lineages');
            $surnames = [];
        }

        return $this->viewResponse($this->module->name() . '::surnames-page', [
            'title'         =>  $title,
            'module'        =>  $this->module,
            'tree'          =>  $tree,
            'initials_list' =>  $initials_list,
            'initial'       =>  $initial,
            'show_all'      =>  $show_all ? 'yes' : 'no',
            'surnames'      =>  $surnames
        ]);
    }
}
