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
use Fisharebest\Webtrees\Factory;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Exceptions\HttpAccessDeniedException;
use Fisharebest\Webtrees\Services\UserService;
use MyArtJaub\Webtrees\Module\Sosa\Services\SosaCalculatorService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Request handler for updating the Sosa de-cujus
 *
 */
class SosaComputeAction implements RequestHandlerInterface
{
    /**
     * @var UserService $user_service
     */
    private $user_service;
    
    /**
     * Constructor for SosaConfigAction Request Handler
     * 
     * @param UserService $user_service
     */
    public function __construct(UserService $user_service)
    {
        $this->user_service = $user_service;
    }
    
    /**
     * {@inheritDoc}
     * @see \Psr\Http\Server\RequestHandlerInterface::handle()
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $tree = $request->getAttribute('tree');
        assert($tree instanceof Tree);
        
        $user_id = (int) ($request->getParsedBody()['user_id'] ?? 0);
        $partial_from = $request->getParsedBody()['partial_from'] ?? null;
        
        if(($user_id == -1 && Auth::isManager($tree)) || Auth::id() == $user_id) {
            $user = $user_id == -1 ? new DefaultUser() : $this->user_service->find($user_id);
            
            /** @var SosaCalculatorService $sosa_calc_service */
            $sosa_calc_service = app()->makeWith(SosaCalculatorService::class, [ 'tree' => $tree, 'user' => $user]);
            
            if($partial_from !== null && $sosa_from = Factory::individual()->make($partial_from, $tree)) {
                $res = $sosa_calc_service->computeFromIndividual($sosa_from);
            } else {
                $res = $sosa_calc_service->computeAll();
            }
            
            return $res ? response('', 200) : response(I18N::translate('An error occurred during Sosa computation.'), 500);
            
        }
        throw new HttpAccessDeniedException(I18N::translate("You do not have permission to modify the user"));
    }
}
