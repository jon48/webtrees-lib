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
use Fisharebest\Webtrees\Services\IndividualListService;
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
    
    /**
     * @var PatronymicLineageModule $module
     */
    private $module;
    
    /**
     * @var IndividualListService $indilist_service
     */
    private $indilist_service;
    
    /**
     * Constructor for SurnamesList Request Handler
     * 
     * @param ModuleService $module_service
     * @param IndividualListService $indilist_service
     */
    public function __construct(
        ModuleService $module_service,
        IndividualListService $indilist_service
    ) {
        $this->module = $module_service->findByInterface(PatronymicLineageModule::class)->first();
        $this->indilist_service = $indilist_service;
    }
    
    /**
     * {@inheritDoc}
     * @see \Psr\Http\Server\RequestHandlerInterface::handle()
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if($this->module === null)
            throw new HttpNotFoundException(I18N::translate('The attached module could not be found.'));
        
        $tree = $request->getAttribute('tree');
        assert($tree instanceof Tree);
        
        $initial = $request->getAttribute('alpha');
        $initials_list = collect($this->indilist_service->surnameAlpha(false, false, I18N::locale()))
            ->reject(function($count, $initial) {
                return $initial === '@' || $initial === ',';
            });
        $show_all = $request->getQueryParams()['show_all'] ?? 'no';
        
        if($show_all === 'yes') {
            $title = I18N::translate('Patronymic Lineages') . ' — ' . I18N::translate('All');
            $surnames = $this->indilist_service->surnames('', '', false, false, I18N::locale());
        }
        else if ($initial !== null && mb_strlen($initial) == 1) {
            $title = I18N::translate('Patronymic Lineages') . ' — ' . $initial;
            $surnames = $this->indilist_service->surnames('', $initial, false, false, I18N::locale());
        }
        else {
            $title =  I18N::translate('Patronymic Lineages');
            $surnames = [];
        }
        
        return $this->viewResponse($this->module->name() . '::surnames-page', [
            'title'         =>  $title,
            'module'        =>  $this->module,
            'tree'          =>  $tree,
            'initials_list' =>  $initials_list,
            'initial'       =>  $initial,
            'show_all'      =>  $show_all,
            'surnames'      =>  $surnames
        ]);
    }

}