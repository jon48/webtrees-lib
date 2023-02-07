<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage GeoDispersion
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2022, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\GeoDispersion\Http\RequestHandlers;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\Http\Exceptions\HttpAccessDeniedException;
use Fisharebest\Webtrees\Http\Exceptions\HttpNotFoundException;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\Services\ModuleService;
use Fisharebest\Webtrees\Services\TreeService;
use MyArtJaub\Webtrees\Module\GeoDispersion\GeoDispersionModule;
use MyArtJaub\Webtrees\Module\GeoDispersion\Services\GeoAnalysisDataService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Request handler for displaying configuration of the module.
 */
class AdminConfigPage implements RequestHandlerInterface
{
    use ViewResponseTrait;

    private ?GeoDispersionModule $module;

    private TreeService $tree_service;

    private GeoAnalysisDataService $geoanalysis_data_service;

    /**
     * Constructor for the AdminConfigPage Request Handler
     *
     * @param ModuleService $module_service
     * @param TreeService $tree_service
     * @param GeoAnalysisDataService $geoanalysis_data_service
     */
    public function __construct(
        ModuleService $module_service,
        TreeService $tree_service,
        GeoAnalysisDataService $geoanalysis_data_service
    ) {
        $this->module = $module_service->findByInterface(GeoDispersionModule::class)->first();
        $this->tree_service = $tree_service;
        $this->geoanalysis_data_service = $geoanalysis_data_service;
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Server\RequestHandlerInterface::handle()
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->layout = 'layouts/administration';

        if ($this->module === null) {
            throw new HttpNotFoundException(I18N::translate('The attached module could not be found.'));
        }

        $user = Validator::attributes($request)->user();

        $all_trees = $this->tree_service->all()->filter(fn(Tree $tree) => Auth::isManager($tree, $user));
        if ($all_trees->count() === 0) {
            throw new HttpAccessDeniedException();
        }

        $tree = Validator::attributes($request)->treeOptional() ?? $all_trees->first();
        assert($tree instanceof Tree);

        $same_tree = fn(Tree $tree_collection): bool => $tree->id() === $tree_collection->id();
        if (!$all_trees->contains($same_tree)) {
            throw new HttpAccessDeniedException();
        }

        return $this->viewResponse($this->module->name() . '::admin/config', [
            'module_name'       =>  $this->module->name(),
            'title'             =>  $this->module->title(),
            'tree'              =>  $tree,
            'other_trees'       =>  $all_trees->reject($same_tree),
            'place_example'     =>  $this->geoanalysis_data_service->placeHierarchyExample($tree),
            'js_script_url'     =>  $this->module->assetUrl('js/geodispersion.min.js')
        ]);
    }
}
