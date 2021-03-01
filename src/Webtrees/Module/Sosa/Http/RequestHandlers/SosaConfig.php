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

namespace MyArtJaub\Webtrees\Module\Sosa\Http\RequestHandlers;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\DefaultUser;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Exceptions\HttpNotFoundException;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\Services\ModuleService;
use MyArtJaub\Webtrees\Module\Sosa\SosaModule;
use MyArtJaub\Webtrees\Module\Sosa\Services\SosaRecordsService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Request handler for configuring the Sosa de-cujus
 */
class SosaConfig implements RequestHandlerInterface
{
    use ViewResponseTrait;

    /**
     * @var SosaModule $module
     */
    private $module;

    /**
     * Constructor for SosaConfig Request Handler
     *
     * @param ModuleService $module_service
     */
    public function __construct(ModuleService $module_service)
    {
        $this->module = $module_service->findByInterface(SosaModule::class)->first();
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

        $tree = $request->getAttribute('tree');
        assert($tree instanceof Tree);

        $users_root = array();
        if (Auth::check()) {
            /** @var \Fisharebest\Webtrees\User $user */
            $user = Auth::user();
            $users_root[] = [
                'user'      => $user,
                'root_id'   => $tree->getUserPreference($user, 'MAJ_SOSA_ROOT_ID'),
                'max_gen'   => $tree->getUserPreference($user, 'MAJ_SOSA_MAX_GEN')
            ];

            if (Auth::isManager($tree)) {
                $default_user = new DefaultUser();
                $users_root[] = [
                    'user' => $default_user,
                    'root_id' => $tree->getUserPreference($default_user, 'MAJ_SOSA_ROOT_ID'),
                    'max_gen'   => $tree->getUserPreference($default_user, 'MAJ_SOSA_MAX_GEN')
                ];
            }
        }

        // Use the system max generations if not set
        $max_gen_system = app(SosaRecordsService::class)->maxSystemGenerations();
        foreach ($users_root as $key => $user_root) {
            $users_root[$key]['max_gen'] = is_numeric($user_root['max_gen']) ?
                (int) $user_root['max_gen'] :
                $max_gen_system;
        };

        return $this->viewResponse($this->module->name() . '::config-page', [
            'module_name'       =>  $this->module->name(),
            'title'             =>  I18N::translate('Sosa Configuration'),
            'tree'              =>  $tree,
            'user_id'           =>  $request->getAttribute('user'),
            'selected_user_id'  =>  (int) ($request->getQueryParams()['user_id'] ?? 0),
            'immediate_compute' =>  ($request->getQueryParams()['compute'] ?? '') == 'yes',
            'users_root'        =>  $users_root
        ]);
    }
}
