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
use Fisharebest\Webtrees\FlashMessages;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Services\UserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Request handler for updating the Sosa de-cujus
 *
 */
class SosaConfigAction implements RequestHandlerInterface
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
        assert($this->user_service instanceof UserService);
        
        $tree = $request->getAttribute('tree');
        assert($tree instanceof Tree);
        
        $params = $request->getParsedBody();
        $user_id = (int) $params['sosa-userid'];
        $root_id = $params['sosa-rootid'] ?? '';
        
        if(Auth::id() == $user_id || ($user_id == -1 && Auth::isManager($tree))) {
            $user = $user_id == -1 ? new DefaultUser() : $this->user_service->find($user_id);
            if($user !== null && $root_indi = Factory::individual()->make($root_id, $tree)) {
                $tree->setUserPreference($user, 'MAJ_SOSA_ROOT_ID', $root_indi->xref());
                FlashMessages::addMessage(I18N::translate('The root individual has been updated.'));
                return redirect(route(SosaConfig::class, [
                    'tree' => $tree->name(),
                    'compute' => 'yes',
                    'user_id' => $user_id
                ]));
            }
        }
        
        FlashMessages::addMessage(I18N::translate('The root individual could not be updated.'), 'danger');
        return redirect(route(SosaConfig::class, ['tree' => $tree->name()]));
    }
}
