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

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\DefaultUser;
use Fisharebest\Webtrees\FlashMessages;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Validator;
use Fisharebest\Webtrees\Services\UserService;
use MyArtJaub\Webtrees\Module\Sosa\Services\SosaRecordsService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Request handler for updating the Sosa de-cujus
 */
class SosaConfigAction implements RequestHandlerInterface
{
    private UserService $user_service;
    private SosaRecordsService $sosa_record_service;

    /**
     * Constructor for SosaConfigAction Request Handler
     *
     * @param UserService $user_service
     * @param SosaRecordsService $sosa_records_service
     */
    public function __construct(UserService $user_service, SosaRecordsService $sosa_records_service)
    {
        $this->user_service = $user_service;
        $this->sosa_record_service = $sosa_records_service;
    }

    /**
     * {@inheritDoc}
     * @see \Psr\Http\Server\RequestHandlerInterface::handle()
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $tree = Validator::attributes($request)->tree();

        $user_id = Validator::parsedBody($request)->integer('sosa-userid', -1);
        $root_id = Validator::parsedBody($request)->isXref()->string('sosa-rootid', '');
        $max_gen = Validator::parsedBody($request)->integer(
            'sosa-maxgen',
            $this->sosa_record_service->maxSystemGenerations()
        );

        if (Auth::id() === $user_id || ($user_id === -1 && Auth::isManager($tree))) {
            $user = $user_id === -1 ? new DefaultUser() : $this->user_service->find($user_id);
            if ($user !== null && ($root_indi = Registry::individualFactory()->make($root_id, $tree)) !== null) {
                $tree->setUserPreference($user, 'MAJ_SOSA_ROOT_ID', $root_indi->xref());
                $tree->setUserPreference($user, 'MAJ_SOSA_MAX_GEN', (string) $max_gen);
                FlashMessages::addMessage(I18N::translate('The root individual has been updated.'));
                return Registry::responseFactory()->redirect(SosaConfig::class, [
                    'tree' => $tree->name(),
                    'compute' => 'yes',
                    'user_id' => $user_id
                ]);
            }
        }

        FlashMessages::addMessage(I18N::translate('The root individual could not be updated.'), 'danger');
        return Registry::responseFactory()->redirect(SosaConfig::class, ['tree' => $tree->name()]);
    }
}
