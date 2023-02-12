<?php

/**
 * webtrees-lib: MyArtJaub library for webtrees
 *
 * @package MyArtJaub\Webtrees
 * @subpackage Sosa
 * @author Jonathan Jaubart <dev@jaubart.com>
 * @copyright Copyright (c) 2009-2023, Jonathan Jaubart
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3
 */

declare(strict_types=1);

namespace MyArtJaub\Webtrees\Module\Sosa\Http\RequestHandlers;

use Fig\Http\Message\StatusCodeInterface;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\DefaultUser;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\Services\UserService;
use MyArtJaub\Webtrees\Module\Sosa\Services\SosaCalculatorService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Request handler for computing the Sosa ancestors
 */
class SosaComputeAction implements RequestHandlerInterface
{
    private UserService $user_service;

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
        $tree = Validator::attributes($request)->tree();

        $user_id = Validator::parsedBody($request)->integer('user_id', Auth::id() ?? 0);
        $partial_from = Validator::parsedBody($request)->isXref()->string('partial_from', '');

        if (($user_id === -1 && Auth::isManager($tree)) || Auth::id() === $user_id) {
            $user = $user_id === -1 ? new DefaultUser() : $this->user_service->find($user_id);

            /** @var SosaCalculatorService $sosa_calc_service */
            $sosa_calc_service = app()->makeWith(SosaCalculatorService::class, [ 'tree' => $tree, 'user' => $user]);

            if (
                $partial_from !== '' &&
                ($sosa_from = Registry::individualFactory()->make($partial_from, $tree)) !== null
            ) {
                $res = $sosa_calc_service->computeFromIndividual($sosa_from);
            } else {
                $res = $sosa_calc_service->computeAll();
            }

            return $res ?
                Registry::responseFactory()->response() :
                Registry::responseFactory()->response(
                    I18N::translate('An error occurred while computing Sosa ancestors.'),
                    StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR
                );
        }
        return Registry::responseFactory()->response(
            I18N::translate('You do not have permission to modify the user.'),
            StatusCodeInterface::STATUS_FORBIDDEN
        );
    }
}
